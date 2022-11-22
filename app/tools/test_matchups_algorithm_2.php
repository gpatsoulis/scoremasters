<?php 


function test( $prev, $fixture )
    {

        //$participants
/*
        if($this->matchups_exists_for_fixture($new_fixture_id)){
            error_log(__METHOD__ . ' matchups exists for fixture: ' . $new_fixture_id);
            return $this;
        }

        if( !$this->previous_matchups_exists( $new_fixture_id ) ){
            $this->initialize_matchups($new_fixture_id);
            return $this;
        }
        */

        //$previous_all_leagues = $this->get_previous_matchups($new_fixture_id);
        //$previous_matchups = $previous_all_leagues['league_id_' . $this->league_id];
        $previous_matchups = $prev;

        //count how many fixtures
        //$fixture_no = $this->fixture_no;
        $fixture_no = $fixture;

        $no_of_participants = count($previous_matchups);

        //is odd
        if ($fixture_no % 2 !== 0) {
            $middle_generator = [1, -1];
            $middle = [];

            for ($i = 1; $i <= ($no_of_participants - 4) / 2; $i++) {
                $middle = array_merge($middle, $middle_generator);
            }

            $transformation_matrix = array_merge([0, -1], $middle, [-1, 2]);

            $next = array();
            foreach ($transformation_matrix as $key => $new_position) {
                $next[$key] = $previous_matchups[$key - $new_position];
            }
        }

        //is even
        if ($fixture_no % 2 === 0) {
            $middle_generator = [-3, 3];
            $middle = [];

            for ($i = 1; $i <= ($no_of_participants - 4) / 2; $i++) {
                $middle = array_merge($middle, $middle_generator);
            }

            $transformation_matrix = array_merge([-3, 0], $middle, [2, 1]);

            $next = array();
            foreach ($transformation_matrix as $key => $new_position) {
                $next[$key] = $previous_matchups[$key - $new_position];
            }

        }

        /*
        $next_matchups = array(
            'fixture_id_' . $new_fixture_id => array(
                'league_id_' . $this->league_id => $next));

        $this->next_matchups = $next_matchups;
        */

        return $next;

    }

    //league_id_2129";a:8:{i:0;i:51;i:1;i:64;i:2;i:53;i:3;i:40;i:4;i:94;i:5;i:97;i:6;i:15;i:7;i:60;}
    //$previous_matchups = [54,83,72,92,24,36,57,98];
    //$previous_matchups = [22,44,48,56,18,20];
    $previous_matchups = [51,64,53,40,94,97,15,60];
    $fixture_no = 0;

    $pairs = test($previous_matchups, $fixture_no);

    var_dump($previous_matchups);
    var_dump($pairs);

    $fixture_no = 1;
    $pairs2 = test($pairs, $fixture_no);

    var_dump($pairs2);
    exit;