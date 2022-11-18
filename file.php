<?php
session_start();
class Config
{
    
/*
|   This class contain the configration information
|   This is completly from user or developer side
*/
    public function request_limit()
    {
        return 10;
    }
    
    public static function interval()
    {
        return 0;
    }

    
}

class Common
{

/*
|   Class to create the common function that can use somany times
|   Storing visit times to an array and the to sessoion
*/
    public static function set_visit($visit_array)
    {
        $visit_array = json_encode($visit_array);
        $_SESSION["visit_time"] = $visit_array;
    }
}

class Analyse
{

/*
|   This is the class write function to analyse the data collected based on request
|   Analysing the chance of attack and time and repeated counts
*/

    public static function chance($mode,$count_values,$total_hit)
    {
    
       $hit_count = $count_values["$mode"];
       $chance = $hit_count/$total_hit * 100;
       if($chance > 90)
       {
           unset($_SESSION["visit_time"]);
       }
       return $chance;
    }
    
    public static function time($prev_time,$times)
    {
        $start = new DateTime($prev_time);
        $end = new DateTime($times);
        $time_diffrence = $end->getTimestamp() - $start->getTimestamp();
        return $time_diffrence;
    }
    
    public static function request_count($request_array)
    {
        $count_values = array_count_values($request_array);
        $mode = array_search(max($count_values), $count_values);
        if($mode == Config::interval())
        {
            $chance = Analyse::chance($mode,$count_values,array_sum($request_array));
            $result = array("message"=>"Bot Ditected",
                            "status"=>true,
                            "request"=>$count_values["$mode"]."/".array_sum($request_array),
                            "chance"=>$chance."%"
                        );
            echo json_encode($result);
        }
    }
}

class Request
{

/*
|   This class is to handle the request from client
|   The first function that will catch the request from the user
*/
    public static function store_request()
    {
        $diffrence_array = array();
        if(isset($_SESSION["visit_time"]))
        {
           $visit_array = json_decode($_SESSION["visit_time"]);
           if(count($visit_array) > Config::request_limit())
           {
              $prev_time = 0;
              foreach($visit_array as $times)
              {
                 if($prev_time == 0)
                 {
                    $prev_time = $times;
                 }
                 else
                 {
                    $time_diffrence = Analyse::time($prev_time,$times);
                    array_push($diffrence_array,$time_diffrence);
                    $prev_time = $times;
                 }
              }
              Analyse::request_count($diffrence_array);
           }
           array_push($visit_array,date("Y-m-d h:i:sa"));
           Common::set_visit($visit_array);
        }
        else
        {
          $visit_array = array(date("Y-m-d h:i:sa"));
          Common::set_visit($visit_array);
        }
    }

    
}

Request::store_request();
?>
