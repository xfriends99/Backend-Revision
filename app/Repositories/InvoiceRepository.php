<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 28/3/2019
 * Time: 5:57 PM
 */
namespace App\Repositories;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Package;

/**
 * Class InvoiceRepository
 * @invoice App\Repositories
 */
class InvoiceRepository extends AbstractRepository
{

    /**
     * PackageRepository constructor.
     * @param Invoice $model
     */
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function filter(array $filters = [])
    {
        /** @var Builder $query */
        $query = $this->model->query();

        $query = $query->select('invoices.*');

        $joins = collect();

        if (isset($filters['tracking']) && $filters['tracking']) {
            $this->addJoin($joins, 'packages', 'invoices.id', 'packages.invoice_id');

            $query = $query->where('packages.tracking', $filters['tracking']);
            
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });
        
        return $query;
    }

    /**
     * @param Collection $joins
     * @param string $table
     * @param string $first
     * @param string $second
     * @param string $join_type
     */
    private function addJoin(Collection &$joins, $table, $first, $second, $join_type = 'inner')
    {
        if (!$joins->has($table)) {
            $joins->put($table, json_encode(compact('first', 'second', 'join_type')));
        }
    }
}
