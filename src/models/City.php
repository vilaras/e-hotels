<?php

namespace models;

class City {
    
    /*
        @input: None
        @output: Array of city names paired with the number of available rooms in them
        @todo: Implement Room::availableInCityNum($city)
    */
    public static function all() {
        $query = DB::query('SELECT DISTINCT Address_City FROM Hotel');
        $cities = [];
        /*  For each result in the query, fetch_assoc() will return an associative array
            like ['amenity' => value]
        */
        while($row = $query->fetch_assoc()) {
            $cities[] = [
                'city' => $row['Address_City'],
                'availableRoomsNum' => Room::availableInCityNum($row['Address_City'])
            ];
        }
        return $cities;
    }

}