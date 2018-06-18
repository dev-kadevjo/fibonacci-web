<?php

namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;
use Kadevjo\Fibonacci\Traits\Loggable; 

class ApiConfig extends Model
{
    use Loggable;
    protected $table = 'api_config';

    protected $guarded = [];


    public function rowBefore()
    {
        $previous = self::where('data_type_id', '=', $this->data_type_id)->where('order', '=', ($this->order - 1))->first();
        if (isset($previous->id)) {
            return $previous->field;
        }

        return '__first__';
    }

    public function relationshipField()
    {
        $options = json_decode($this->details);

        return @$options->column;
    }

    /**
     * Check if this field is the current filter.
     *
     * @return bool True if this is the current filter, false otherwise
     */
    public function isCurrentSortField()
    {
        return isset($_GET['order_by']) && $_GET['order_by'] == $this->field;
    }

    /**
     * Build the URL to sort data type by this field.
     *
     * @return string Built URL
     */
    public function sortByUrl()
    {
        $params = $_GET;
        $isDesc = isset($params['sort_order']) && $params['sort_order'] != 'asc';
        if ($this->isCurrentSortField() && $isDesc) {
            $params['sort_order'] = 'asc';
        } else {
            $params['sort_order'] = 'desc';
        }
        $params['order_by'] = $this->field;

        return url()->current().'?'.http_build_query($params);
    }


    public function makeJson($requestData)
    { 
        return '{
                "browse": {"enable": '.( (isset($requestData["allow_browse"])) ? 'true' : 'false' ).',"secure": '.( (isset($requestData["secure_browse"])) ? 'true' : 'false' ).'},
                "read": {"enable": '.( (isset($requestData["allow_read"])) ? 'true' : 'false' ).',"secure": '.( (isset($requestData["secure_read"])) ? 'true' : 'false' ).'}, 
                "edit": {"enable": '.( (isset($requestData["allow_edit"])) ? 'true' : 'false' ).',"secure": '.( (isset($requestData["secure_edit"])) ? 'true' : 'false' ).'}, 
                "add": {"enable": '.( (isset($requestData["allow_add"])) ? 'true' : 'false' ).',"secure": '.( (isset($requestData["secure_add"])) ? 'true' : 'false' ).'}, 
                "delete": {"enable": '.( (isset($requestData["allow_delete"])) ? 'true' : 'false' ).',"secure": '.( (isset($requestData["secure_delete"])) ? 'true' : 'false' ).'} 
            }';
    }
}