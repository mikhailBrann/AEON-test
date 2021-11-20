<?php

class User {

    // GENERAL

    public static function user_info($data) {
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='".$user_id."'";
        else if ($phone) $where = "phone='".$phone."'";
        else return [];
        // info
        $q = DB::query("SELECT user_id, first_name, last_name, middle_name, email, gender_id, count_notifications FROM users WHERE ".$where." LIMIT 1;") or die (DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'middle_name' => $row['middle_name'],
                'gender_id' => (int) $row['gender_id'],
                'email' => $row['email'],
                'phone' => (int) $row['phone'],
                'phone_str' => phone_formatting($row['phone']),
                'count_notifications' => (int) $row['count_notifications']
            ];
        } else {
            return [
                'id' => 0,
                'first_name' => '',
                'last_name' => '',
                'middle_name' => '',
                'gender_id' => 0,
                'email' => '',
                'phone' => '',
                'phone_str' => '',
                'count_notifications' => 0
            ];
        }
    }

    public static function user_get_or_create($phone) {
        // validate
        $user = User::user_info(['phone' => $phone]);
        $user_id = $user['id'];
        // create
        if (!$user_id) {
            DB::query("INSERT INTO users (status_access, phone, created) VALUES ('3', '".$phone."', '".Session::$ts."');") or die (DB::error());
            $user_id = DB::insert_id();
        }
        // output
        return $user_id;
    }

    // TEST
    public static function owner_update($data = []) {
        // your code here ...
        
        if (isset($data['first_name']) || isset($data['last_name']) || isset($data['phone']) || isset($data['middle_name']) || isset($data['email'])) {
            // vars
            $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
            $response_text = "";

            if(isset($data['first_name'])) {
                if(!empty($data['first_name'])) {
                    $first_name = htmlspecialchars($data['first_name']); 
                    $first_name = "`first_name` = '{$first_name}',";
                    $response_text .= $first_name;
                } else {
                    response(error_response(2002, 'first_name cannot be empty')); 
                }
            }
            
            if(isset($data['last_name'])) {
                if(!empty($data['last_name'])) {
                    $last_name = htmlspecialchars($data['last_name']);
                    $last_name = "`last_name` = '{$last_name}',";
                    $response_text .= $last_name; 
                } else {
                    response(error_response(2003, 'last_name cannot be empty')); 
                }
            }

            if(isset($data['phone'])) {
                if (!empty($data['phone'])) {
                    $phone_clear = preg_replace('~[^\d]+~', '', $data['phone']);
                    $phone_first_nim = mb_substr($phone_clear, 0, 1);
                    $phone_length = strlen($phone_clear);
                    if($phone_first_nim == '7' && $phone_length == 11) {
                        $phone_clear = "`phone` = '{$phone_clear}',";
                        $response_text .= $phone_clear;
                    } else {
                        response(error_response(2005, 'phone number starts with 7 and no longer than 11 characters'));
                    }
                } else {
                    response(error_response(2004, 'phone cannot be empty'));
                }
            }

            if(isset($data['middle_name'])) {
                $middle_name = htmlspecialchars($data['middle_name']);
                $middle_name = "`middle_name` = '{$middle_name}',";
                $response_text .= $middle_name;
            }

            if(isset($data['email'])) {
                $email = strtolower(htmlspecialchars($data['email']));
                $email = "`email` = '{$email}',";
                $response_text .= $email;
            }

            // добавляем дату апдейта
            $date_now = strtotime("now");
            $response_text .= "`updated` = '{$date_now}',";

            //убиваем крайнюю запятую для запроса
            if($response_text[strlen($response_text)-1] = ',')  $response_text = mb_substr($response_text, 0, -1);
          
            DB::query("UPDATE `users` SET {$response_text} WHERE `user_id`={$user_id}") or die (DB::error());

        } else {
            response(error_response(2001, 'не указанны поля для изменения'));
        }

    }

    public static function user_get_notifications($data = []) {
        // your code here ...
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        $response_text = "";

        if($data['not_viewed'] == "true") {
            $response_text = "SELECT * FROM `user_notifications` WHERE `viewed`=0 AND `user_id`={$user_id}";
        } else {
            $response_text = "SELECT * FROM `user_notifications` WHERE `user_id`={$user_id}";
        }
    
        $response = DB::query($response_text) or die (DB::error());
        return DB::fetch_all($response);
        
    }

    public static function user_read_notifications($data = []) {
        // your code here ...
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        DB::query("UPDATE `user_notifications` SET `viewed`=1 WHERE `user_id`={$user_id}") or die (DB::error());
    }

}
