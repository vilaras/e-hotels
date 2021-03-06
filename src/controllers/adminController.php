<?php

namespace controllers;

use \models\HotelGroup as HotelGroup;
use \models\Employee as Employee;
use \models\Customer as Customer;

class adminController {

    public static function index() {
        $data = [
            'hotel_groups' => HotelGroup::all(),
            'employees' => Employee::all(),
            'customers' => Customer::all(),
            'view' => $_GET['view'] ?? 'groups',
        ];
        return $data;
    }

}