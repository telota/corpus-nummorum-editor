<?php

namespace App\Http\Controllers\dbi\entities;

use App\Http\Controllers\dbi\dbiInterface;
use DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\standardMail;


class users implements dbiInterface  {

    // Controller-Functions ------------------------------------------------------------------

    public function select ($user, $id) {
        $query = DB::table(config('dbi.tablenames.users')) -> select([
            'id',
            DB::Raw('IF(level = 0, CONCAT("GELÖSCHT (", updated_at, ")"), name) AS name'),
            'email',
            'level as level',
            DB::Raw('IF(level = 0, CONCAT("GELÖSCHT (", updated_at, ")"), lastname) AS lastname'),
            DB::Raw('IF(level = 0, CONCAT("GELÖSCHT (", updated_at, ")"), firstname) AS firstname'),
            'last_login AS last_login',
            'created_at AS created_at'
        ]);

        // Hide old Amintoolusers
        $query -> where([
            ['firstname', '!=', 'Admintooluser'],
            ['name', 'NOT LIKE', 'old_%']
        ]);

        // Set condition if ID is given
        if (!empty($id)) { $query -> where('id', $id); }

        $dbi = $query -> orderByRaw('level = 0, lastname ASC, email ASC') -> get();
        $items = json_decode($dbi, TRUE);

        return $items;
    }

    public function input ($user, $input) {
        $validation = $this -> validateInput($input);

        if(empty($validation['error'][0])) {
            $input = DB::table(config('dbi.tablenames.users')) -> select([
                'id',
                'name',
                'email',
                'level as previous_level',
                'lastname',
                'firstname'
            ])
            -> where('id', $input['id']) -> get();
            $input = json_decode($input, true);

            if (empty($input[0])) {
                return ['error' => 'Unbekannter Nutzer!'];
            }
            else {
                $input = $input[0];
                $input['level'] = $validation['input']['level'];
            }

            // Update User in DB
            if ($input['previous_level'] !== $input['level']) {
                DB::table(config('dbi.tablenames.users')) -> where('id', $input['id']) -> update(['level' => $input['level']]);

                // Send Email if User has been upgraded
                if ($input['previous_level'] === 1 && $input['level'] > 1) {
                    Mail::to($input['email']) -> send(
                        new standardMail($input, [
                            'subject' => 'Registrierung bestätigt',
                            'view' => 'user_upgraded'
                        ])
                    );
                }
                return $input['id'];
            }
            else {
                return ['error' => [['de' => 'Keine zu speichernde Änderung!']]];
            }
        }
        else {
            return ['error' => $validation['error']];
        }
    }

    public function delete ($user, $input) {
        DB::transaction(function () use ($input) {
            //DB::table(config('dbi.tablenames.users')) -> where('id', $input['id']) -> delete();
            DB::table(config('dbi.tablenames.users')) -> where('id', $input['id']) -> update([
                'level'  => 0,
                'name'          => 'deleted',
                'firstname'     => null,
                'lastname'      => null,
                'country'       => null
            ]);
        });

        return true;
    }

    public function connect ($user, $input) {
        die (abort(404, 'Not supported!'));
    }


    // Helper-Functions ------------------------------------------------------------------

    public function validateInput ($input) {
        if (empty($input['id'])) {
            $error[] = ['de' => 'Keine User-ID!'];
        }
        if (empty($input['level'])) {
            $error[] = ['de' => 'Kein User-Level!'];
        }

        // Return validated input
        if (empty($error)) {
            return ['input' => $input];
        }
        // Return Error
        else {
            return ['error' => $error];
        }
    }
}
