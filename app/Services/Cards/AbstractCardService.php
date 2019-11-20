<?php

namespace App\Services\Cards;

use App\Events\PackageWasDebited;
use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Models\Transaction;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionTypeRepository;
use App\Services\Cards\Entities\AddCardEntity;
use App\Services\Cards\Entities\CardEntity;
use App\Repositories\CardPaymentGatewayRepository;
use App\Repositories\CardRepository;
use App\Services\Cards\Entities\ProcessConfirmationEntity;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Interfaces\RequestInterface;
use App\Services\Cards\Interfaces\ResponseInterface;
use App\Services\Cards\Interfaces\ValidateInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class AbstractCardService
{
    /** @var  CardRepository */
    protected $cardRepository;

    /** @var  CardPaymentGatewayRepository */
    protected $cardPaymentGatewayRepository;

    /** @var TransactionTypeRepository  */
    protected $transactionTypeRepository;

    /** @var PaymentMethodRepository  */
    protected $paymentMethodRepository;

    /** @var InvoiceRepository */
    protected $invoiceRepository;

    /** @var TransactionRepository */
    protected $transactionRepository;

    /** @var PaymentGateway  */
    protected $paymentGateway;

    /**
     * AbstractCardService constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->cardRepository = app(CardRepository::class);
        $this->cardPaymentGatewayRepository = app(CardPaymentGatewayRepository::class);
        $this->transactionTypeRepository = app(TransactionTypeRepository::class);
        $this->paymentMethodRepository = app(PaymentMethodRepository::class);
        $this->invoiceRepository = app(InvoiceRepository::class);
        $this->transactionRepository = app(TransactionRepository::class);

        $this->paymentGateway = $paymentGateway;
    }

    /**
     * @param $user
     * @param array $attributes
     * @return Card
     * @throws Exception
     */
    public function addCard(User $user, array $attributes = [])
    {
        /** @var AddCardEntity $addCardEntity */
        $addCardEntity = $this->validateAddCard($attributes);

        /** @var mixed $response */
        $response = $this->makeAddCard($user, $addCardEntity);

        logger("[".$this->paymentGateway->name."] Add Card Response:");
        logger(json_encode($response));

        /** @var CardEntity $cardEntity */
        $cardEntity = $this->parseAddCard($user, $response);

        try {
            DB::beginTransaction();

            /** @var Card $card */
            $card = $this->cardRepository->create([
                'user_id' =>  $cardEntity->getUserId(),
                'card_brand_id' => $cardEntity->getCardBrandId(),
                'name' => $cardEntity->getName(),
                'token' => $cardEntity->getToken(),
                'expiry_year' => $cardEntity->getExpiryYear(),
                'expiry_month' => $cardEntity->getExpiryMonth(),
                'number' => $cardEntity->getNumber()
            ]);

            // Prepare array to create card gateway model
            $this->cardPaymentGatewayRepository->create([
                'card_id' => $card->id,
                'payment_gateway_id' => $this->paymentGateway->id,
                'token' => $cardEntity->getToken(),
                'details' => json_encode($cardEntity->getDetails())
            ]);

            // Mark card by default if the user does not have any
            if (!$defaultCard = $user->getDefaultCard()) {
                $this->cardRepository->update($card, ['default' => true]);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            throw new Exception ($exception->getMessage());
        }

        return $card;
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @throws Exception
     * @return Transaction
     */
    public function debit(Card $card, Invoice $invoice)
    {
        /** @var mixed $response */
        $response = $this->makeDebit($card, $invoice);

        logger("[".$this->paymentGateway->name."] Debit Response:");
        logger(json_encode($response));

        /** @var TransactionEntity $transactionEntity */
        $transactionEntity = $this->parseDebit($response);

        /** @var TransactionType $transactionType */
        if (!$transactionType = $this->transactionTypeRepository->getByKey('debit')) {
            throw new Exception ('Type debit not found');
        }

        /** @var PaymentMethod $paymentMethod */
        if (!$paymentMethod = $this->paymentMethodRepository->getByPaymentGatewayAndKey($this->paymentGateway, 'credit')) {
            throw new Exception ('Payment method credit not found');
        }

        /** @var TransactionStatus $transactionStatus */
        $transactionStatus = $transactionEntity->getTransactionStatus();

        // Update invoice status
        $this->invoiceRepository->update($invoice, ['state' => $transactionStatus->key]);

        // If state is approved -> update charged date of invoice
        if ($transactionStatus->isApproved()) {
            $this->invoiceRepository->update($invoice, [
                'charged_at' => $transactionEntity->getDate()
            ]);
        }

        // Create transaction
        return $this->transactionRepository->create([
            'invoice_id' => $invoice->id,
            'card_id' => $card->id,
            'payment_method_id' => $paymentMethod->id,
            'transaction_status_id' => $transactionStatus->id,
            'transaction_type_id' => $transactionType->id,
            'amount' => $transactionEntity->getAmount(),
            'external_id' => $transactionEntity->getId(),
            'details' => $transactionEntity->getDetails()
        ]);
    }


    /**
     * @param Transaction $transaction
     * @return \Illuminate\Database\Eloquent\Model
     * @throws Exception
     */
    public function refund(Transaction $transaction)
    {
        $response = $this->makeRefund($transaction);

        logger("[".$this->paymentGateway->name."] Refund Response:");
        logger(json_encode($response));

        /** @var TransactionEntity $transactionEntity */
        $transactionEntity = $this->parseMakeRefund($response);

        /** @var TransactionType $transactionType */
        if (!$transactionType = $this->transactionTypeRepository->getByKey('refund')) {
            throw new Exception ('Type refund not found');
        }

        /** @var PaymentMethod $paymentMethod */
        if (!$paymentMethod = $this->paymentMethodRepository->getByPaymentGatewayAndKey($this->paymentGateway, 'credit')) {
            throw new Exception ('Payment method credit not found');
        }

        /** @var TransactionStatus $transactionStatus */
        $transactionStatus = $transactionEntity->getTransactionStatus();

        /** @var Invoice $invoice */
        $invoice = $transaction->invoice;

        // Create transaction
        /** @var Transaction $transaction */
        return $this->transactionRepository->create([
            'invoice_id' => $invoice->id,
            'card_id' => $transaction->card_id,
            'payment_method_id' => $paymentMethod->id,
            'transaction_status_id' => $transactionStatus->id,
            'transaction_type_id' => $transactionType->id,
            'amount' => $transactionEntity->getAmount(),
            'external_id' => $transactionEntity->getId(),
            'details' => $transactionEntity->getDetails()
        ]);

    }

    /**
     * @param Card $card
     * @return boolean|null|mixed
     */
    public function deleteCard(Card $card)
    {
        $this->validateDeleteCard($card);

        /** @var object $response */
        $response = $this->makeDeleteCard($card);

        logger("[".$this->paymentGateway->name."] Delete Response:");
        logger(json_encode($response));

        $this->parseMakeDeleteCard($response);

        try {
            $this->cardRepository->delete($card);

            return true;
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }

        return false;

    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function processConfirmation(Request $request)
    {
        /** @var ProcessConfirmationEntity $processConfirmationEntity */
        $processConfirmationEntity = $this->validateProcessConfirmation($request);

        /** @var TransactionEntity $transactionEntity */
        $transactionEntity = $this->parseProcessConfirmation($processConfirmationEntity);

        /** @var Transaction $transaction */
        if (!$transaction = $this->transactionRepository->filter(['external_id' => $transactionEntity->getId()])->first()) {
            throw new Exception ("Transaction ID {$transactionEntity->getId()} not found");
        }

        /** @var TransactionStatus $transactionStatus */
        $transactionStatus = $transactionEntity->getTransactionStatus();

        /** @var Invoice $invoice */
        $invoice = $transaction->invoice;

        // Update invoice status
        $this->invoiceRepository->update($invoice, ['state' => $transactionStatus->key]);

        // Update transaction status
        $this->transactionRepository->update($transaction, ['transaction_status_id' => $transactionStatus->id]);

        // If state is approved -> update charged date of invoice
        if ($transactionStatus->isApproved()) {
            if (isset($webhookNotification->transaction->updatedAt)) {
                $this->invoiceRepository->update($invoice, [
                    'charged_at' => $transactionEntity->getDate()
                ]);
            }

            event(new PackageWasDebited($invoice->packages->first()));
        }
    }

    /**
     * @return string|false
     * @throws GatewayException|Exception
     */
    public function generateClientToken()
    {
        return $this->makeGenerateClientToken();
    }

    public function markAsDefault(Card $card)
    {
        $this->cardRepository->updateMultiple(['default' => false], ['user_id' => $card->user_id]);

        return $this->cardRepository->markAsDefault($card);
    }

    /**
     * @param array $attributes
     * @return AddCardEntity
     */
    protected abstract function validateAddCard(array $attributes);

    /**
     * @param User $user
     * @param AddCardEntity $addCardEntity
     * @return mixed|object|array
     */
    protected abstract function makeAddCard(User $user, AddCardEntity $addCardEntity);

    /**
     * @param User $user
     * @param $response
     * @return CardEntity
     */
    protected abstract function parseAddCard(User $user, $response);

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return mixed
     */
    protected abstract function makeDebit(Card $card, Invoice $invoice);

    /**
     * @param mixed|object|array $response
     * @return TransactionEntity
     */
    protected abstract function parseDebit($response);

    /**
     * @param Request $request
     * @return ProcessConfirmationEntity
     */
    protected abstract function validateProcessConfirmation(Request $request);

    /**
     * @param ProcessConfirmationEntity $processConfirmationEntity
     * @return TransactionEntity
     * @throws GatewayException
     */
    protected abstract function parseProcessConfirmation(ProcessConfirmationEntity $processConfirmationEntity);

    /**
     * @return mixed|string|bool
     * @throws GatewayException|Exception
     */
    protected abstract function makeGenerateClientToken();

    /**
     * @param Card $card
     * @return mixed
     */
    protected abstract function validateDeleteCard(Card $card);

    /**
     * @param Card $card
     * @return mixed
     */
    protected abstract function makeDeleteCard(Card $card);

    /**
     * @param $response
     * @return mixed
     */
    protected abstract function parseMakeDeleteCard($response);

    /**
     * @param Transaction $transaction
     * @return mixed|array|object
     */
    protected abstract function makeRefund(Transaction $transaction);

    /**
     * @param mixed|array|object $response
     * @return mixed
     */
    protected abstract function parseMakeRefund($response);

    /**
     * @return ValidateInterface
     */
    public abstract function getValidateInstance();

    /**
     * @return RequestInterface
     */
    public abstract function getRequestInstance();

    /**
     * @return ResponseInterface
     */
    public abstract function getResponseInstance();

    /**
     * @param float $amount
     * @return float
     */
    public abstract function getAmount($amount);
}
