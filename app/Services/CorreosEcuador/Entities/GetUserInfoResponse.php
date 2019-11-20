<?php

namespace App\Services\CorreosEcuador\Entities;

use App\Services\HttpRequests\AbstractResponse;
use Carbon\Carbon;

class GetUserInfoResponse extends AbstractResponse
{
    /** @var string */
    private $error;

    /** @var int|null */
    private $id_persona;

    /** @var string */
    private $nombre_1;

    /** @var string */
    private $nombre_2;

    /** @var string */
    private $apellido_1;

    /** @var string */
    private $apellido_2;

    /** @var string */
    private $fecha_nacimiento;

    /** @var string */
    private $cedula;

    /** @var string */
    private $ruc;

    /** @var string */
    private $razon_social;

    /** @var string */
    private $pasaporte;

    /** @var string */
    private $mail_personal;

    /** @var string */
    private $celular;

    /** @var string */
    private $convencional;

    /** @var int */
    private $ubi_geo_id;

    /** @var string */
    private $calle_1;

    /** @var string */
    private $numeracion;

    /** @var string */
    private $calle_2;

    /** @var string */
    private $referencia;

    /** @var string */
    private $codigo_postal;

    /** @var int */
    private $usuarioAvisalo;

    public function initialize(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->error;
    }

    /**
     * @param string
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->error);
    }

    public function getIdPersona()
    {
        return $this->id_persona;
    }

    public function setIdPersona($value)
    {
        $this->id_persona = intval($value);
    }

    public function getNombre1()
    {
        return $this->nombre_1;
    }

    public function setNombre1($value)
    {
        $this->nombre_1 = $value;
    }

    public function getNombre2()
    {
        return $this->nombre_2;
    }

    public function setNombre2($value)
    {
        $this->nombre_2 = $value;
    }

    public function getApellido1()
    {
        return $this->apellido_1;
    }

    public function setApellido1($value)
    {
        $this->apellido_1 = $value;
    }

    public function getApellido2()
    {
        return $this->apellido_2;
    }

    public function setApellido2($value)
    {
        $this->apellido_2 = $value;
    }

    public function getFechaNacimiento()
    {
        return $this->fecha_nacimiento;
    }

    public function getFechaNacimientoAsCarbon()
    {
        return Carbon::parse($this->fecha_nacimiento);
    }

    public function setFechaNacimiento($value)
    {
        $this->fecha_nacimiento = $value;
    }

    public function getCedula()
    {
        return $this->cedula;
    }

    public function setCedula($value)
    {
        $this->cedula = $value;
    }

    public function getRuc()
    {
        return $this->ruc;
    }

    public function setRuc($value)
    {
        $this->ruc = $value;
    }

    public function getRazonSocial()
    {
        return $this->razon_social;
    }

    public function setRazonSocial($value)
    {
        $this->razon_social = $value;
    }

    public function getPasaporte()
    {
        return $this->pasaporte;
    }

    public function setPasaporte($value)
    {
        $this->pasaporte = $value;
    }

    public function getMailPersonal()
    {
        return $this->mail_personal;
    }

    public function setMailPersonal($value)
    {
        $this->mail_personal = $value;
    }

    public function getCelular()
    {
        return $this->celular;
    }

    public function setCelular($value)
    {
        $this->celular = $value;
    }

    public function getConvencional()
    {
        return $this->convencional;
    }

    public function setConvencional($value)
    {
        $this->convencional = $value;
    }

    public function getUbiGeoId()
    {
        return $this->ubi_geo_id;
    }

    public function setUbiGeoId($value)
    {
        $this->ubi_geo_id = $value;
    }

    public function getCalle1()
    {
        return $this->calle_1;
    }

    public function setCalle1($value)
    {
        $this->calle_1 = $value;
    }

    public function getNumeracion()
    {
        return $this->numeracion;
    }

    public function setNumeracion($value)
    {
        $this->numeracion = $value;
    }

    public function getCalle2()
    {
        return $this->calle_2;
    }

    public function setCalle2($value)
    {
        $this->calle_2 = $value;
    }

    public function getReferencia()
    {
        return $this->referencia;
    }

    public function setReferencia($value)
    {
        $this->referencia = $value;
    }

    public function getCodigoPostal()
    {
        return $this->codigo_postal;
    }

    public function setCodigoPostal($value)
    {
        $this->codigo_postal = $value;
    }

    public function getUsuarioAvisalo()
    {
        return $this->usuarioAvisalo;
    }

    public function setUsuarioAvisalo($value)
    {
        $this->usuarioAvisalo = intval($value);
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return $this->usuarioAvisalo === 1;
    }

    public function getFullFirstName()
    {
        $name = $this->getNombre1();
        if ($this->getNombre2()) {
            $name .= ' ' . $this->getNombre2();
        }

        return $name;
    }

    public function getFullLastName()
    {
        $name = $this->getApellido1();
        if ($this->getApellido2()) {
            $name .= ' ' . $this->getApellido2();
        }

        return $name;
    }

    public function isFechaNacimientoEqualTo(Carbon $other)
    {
        return Carbon::parse($this->getFechaNacimiento())->isSameDay($other);
    }

    /**
     * @param $born_at
     * @return Carbon|\Carbon\CarbonInterface|false
     */
    public function parseBornAtFromCalendar($born_at)
    {
        return Carbon::createFromFormat('d/m/Y', $born_at);
    }
}