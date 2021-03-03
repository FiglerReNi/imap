<?php
include 'MailReader.php';
include 'config.php';
include 'PDOConnect.php';


try {
    $pdoConn = new PDOConnect('TWO');
    $emails = new MailReader(MAIL_READ_HOST, MAIL_READ_PORT, MAIL_USERNAME, MAIL_PASSWORD);

    $contents = $emails->getContent(2, 'stephany85@gmail.com');

    foreach ($contents as $content) {
        $content = (imap_base64($content));
        $content = array_filter(explode(PHP_EOL, $content));
        if (!empty($content) && strpos($content[0], 'Sz') > -1) {
            insertData($content, $pdoConn);
        }
    }

    $emails->removeEmail();
} catch (Exception $e) {
    echo $e;
}
function insertData($content, $pdoConn)
{
    $item = array();
    $query = "INSERT IGNORE INTO imap_teszt (szamok) VALUES ";
    foreach ($content as $key => $value) {
        if (!in_array($key, array(0, count($content) - 1))) {
            $query .= "(:kulcs" . $key . ")";
            if ($key < count($content) - 2) $query .= ", ";
            $item['kulcs' . $key] = $value;
        }
    }
    $pdoConn->executestatement($query, $item);
}
