<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    public function add($num1, $num2)
    {
        return $num1 + $num2;
    }

    public function sub($num1, $num2)
    {
        return $num1 - $num2;
    }
}
