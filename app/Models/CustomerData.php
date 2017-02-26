<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Created by PhpStorm.
 * User: Ivan Atanasov email: atanassoff.i@gmail.com
 * Date: 22.11.14
 * Time: 10:33
 */

class CustomerData extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'customers';

    /**
     * relationship to return the main info from
     * users table for the current customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getUser() {
        return $this->belongsTo('App\\User', 'user_id');
    }

    /**
     * relationship to return the user who created this record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy() {
        return $this->belongsTo('App\\User', 'created_by');
    }
}