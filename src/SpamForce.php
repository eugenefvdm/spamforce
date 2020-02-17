<?php

namespace eugenevdm\Exim;

class SpamForce
{

    public static function run()
    {
        // Get a list of all messages in the Exim queue
        $list    = "/usr/sbin/exim -bp";
        $subject = shell_exec("$list");
        $pattern = "/.\s+.+\s(.+)\s</";
        preg_match_all($pattern, $subject, $matches);

        $report = "There are " . count($matches[1]) . " messages in the queue.\n";

        // Loop through all messages and obtain the body -Mvb (b = body)
        foreach ($matches[1] as $match) {
            $retrieve = "/usr/sbin/exim -Mvb $match";
            $message  = shell_exec($retrieve);
            $result   = self::checkForSpam($message, $match);
            if ($result == true) {
                $delete = "/usr/sbin/exim -Mrm $match";
                shell_exec($delete);
                $report .= "Message $match was reported and deleted.\n";
            }
        }
        return ($report);
    }

    static function checkForSpam($message, $match)
    {
        $spam_notice = "has identified this incoming email as possible spam.";
        $pos         = strpos($message, $spam_notice);
        if ($pos === false) return false;
        $spammer = self::cut($message);
        if (!$spammer->$message) {
            echo "Error: $match has no content, aborting!\n";
            return false;
        }
        self::submitReport($spammer);
        return true;
    }

    static function cut($message)
    {
        preg_match("/From: (.+)/", $message, $from);
        preg_match("/To: (.+)/", $message, $to);
        preg_match("/Date: (.+)/", $message, $date);
        preg_match("/X-Spam-Status: (.+)/", $message, $status);
        preg_match("/(Return-path:.*)/s", $message, $message);
        $spammer          = new Spammer();
        $spammer->from    = $from[1];
        $spammer->to      = $to[1];
        $spammer->date    = $date[1];
        $spammer->status  = $status[1];
        $spammer->message = $message[1];
        return $spammer;
    }

    static function submitReport($spammer)
    {
        $spamcop_submit = getenv('SPAMCOP_SUBMIT');
        $spamforce_from = getenv('SPAMFORCE_FROM');
        $spamforce_cc   = getenv('SPAMFORCE_CC');
        $headers        = "From:$spamforce_from\r\n";
        if ($spamforce_cc) {
            $headers .= "Cc: $spamforce_cc\r\n";
        }
        $message = "Message body intentionally made blank";
        mail($spamcop_submit,
            "Reporting SPAM from {$spammer->from} to {$spammer->to} date {$spammer->date} status {$spammer->status}",
            $message,
            $headers);
    }

}

class Spammer
{
    public $from;
    public $to;
    public $date;
    public $status;
    public $message;
}
