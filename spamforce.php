<?php

namespace eugenevdm;

class SpamForce {

    public static function run() {
        // Get a list of all messages in the Exim queue
        $list    = "/usr/sbin/exim -bp";
        $subject = shell_exec("$list");
        $pattern = "/.\s+.+\s(.+)\s</";
        preg_match_all($pattern, $subject, $matches);

        echo "There are " . count($matches[1]) . " messages in the queue.\n";

        // Loop through all messages and obtain the body -Mvb (b = body)
        foreach ($matches[1] as $match) {
            $retrieve = "/usr/sbin/exim -Mvb $match";
            $message  = shell_exec($retrieve);
            $result   = self::checkForSpam($message, $match);
            if ($result == true) {
                $delete = "/usr/sbin/exim -Mrm $match";
                shell_exec($delete);
                echo "Message $match was reported and deleted.\n";
            }
        }

    }

    static function checkForSpam($message, $match)
    {
        $spam_notice = "has identified this incoming email as possible spam.";
        $pos         = strpos($message, $spam_notice);
        if ($pos === false) return false;
        $message = self::cut($message);
        if (!$message) {
            echo "Error: $match has no content, aborting!\n";
            return false;
        }
        self::report($message);
        return true;
    }

    static function cut($message)
    {
        preg_match("/From: (.+)/", $message, $from);
        preg_match("/To: (.+)/", $message, $to);
        preg_match("/Date: (.+)/", $message, $date);
        preg_match("/X-Spam-Status: (.+)/", $message, $status);
        preg_match("/(Return-path:.*)/s", $message, $cut);
        return $cut[1];
    }

    static  function report($message)
    {
        $spamcop = getenv('SPAMCOP_SUBMIT');
        $from    = getenv('SPAMFORCE_FROM');
        $cc      = getenv('SPAMFORCE_CC');
        $headers = "From:$from\r\n";
        if ($cc) {
            $headers .= "Cc: $cc\r\n";
        }
        mail($spamcop, "Reporting SPAM", $message, $headers);
    }

}

