<?php
 /* Enter your code here. Read input from STDIN. Print output to STDOUT */
    
    $stdin = fopen('php://stdin', 'r');
    translate( 16543 );
    function translate( $input ){
        $translation = array(
            1 => "one",
            2 => "two",
            3 => "three",
            4 => "four",
            5 => "five",
            6 => "six" ,
            7 => "seven",
            8 => "eight",
            9 => "nine",
            10 => "ten",
            11 => "eleven",
            12 => "twelve",
            13 => "thirteen", 
            14 => "fourteen" , 
            15 => "fifteen" ,
            16 => "sixteen"  ,
            17 => "seventeen" ,
            18 => "eighteen"  ,
            19 => "nineteen" ,
            20 => "twenty",
            30 => "thirty",
            40 => "forty",
            50 => "fifty",
            60 => "sixty",
            70 => "seventy",
            80 => "eighty",
            90 => "ninety",
        );

        $input_arr = my_split( $input );
        

        foreach( $input_arr as $k => $number ){
            $trans_str = array();
            if( $number[0] != 0 ){
                
                $trans_str[0] = $translation[ floor( (int)$number[0] ) ] . " hundred";
            }

            if( $number[1] != 0 ){
                if( $number[1] == 1 ){
                    $trans_str[1] = $translation[ 10+ $number[2]];
                }else{
                    $trans_str[1] = $translation[ floor( (int)$number[1] ) * 10 ];
                }
            }

            if( $number[2] != 0 && $number[1] != 1){
                //各位
                $trans_str[1] = isset( $trans_str[1] ) ? $trans_str[1] . " " .$translation[$number[2]] : $translation[$number[2]];
            }
            
            
            $input_arr[$k] = implode(" and " , $trans_str );
        }
        

        
        $output = implode( " thousand ", $input_arr );
        echo $output;
    }

    function my_split( $input ){
        $max_length = 6;
        $output = array();
        $input_str = str_repeat( "0", $max_length - strlen( (string)$input ) ) . (string) $input;
        for( $i = $max_length; $i > 1; $i -= 3 ){
            $str =  substr( $input_str, $i - 3, 3 );
            if( $str!= "000" ){
                $output[] = substr( $input_str, $i - 3, 3 );
            }
        }
        return array_reverse($output);
    }