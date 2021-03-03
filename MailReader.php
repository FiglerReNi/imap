<?php


class MailReader
{
    private $allEmail;
    private $attachements = array();
    private $emailNumbers;

    public function __construct($host, $port, $user, $pass)
    {
        $this->allEmail = imap_open("{" . $host . ":" . $port . "/ssl}", $user, $pass);
        if (!$this->allEmail) {
            throw new Exception('Kapcsolat hiba: ' . imap_last_error());
        }
    }

    public function getAllEmail()
    {
        return $this->allEmail;
    }

    private function fetchContent($msgNum, $section)
    {
        $content = imap_fetchbody($this->allEmail, $msgNum, $section);
        return $content;
    }

    public function getContent($section, $from = "")
    {
        if (!empty($from)) {
            $this->emailNumbers = imap_search($this->allEmail, 'FROM "' . $from . '"');
            var_dump($this->emailNumbers);
            echo '</br>';
            if (!$this->emailNumbers) {
                throw new Exception('Search failed: ' . imap_last_error());
            }
            foreach ($this->emailNumbers as $key => $value) {
                $this->attachements[$key] = $this->fetchContent($value, $section);
            }
        } else {
            $this->emailNumbers = imap_num_msg($this->allEmail);
            $i = 0;
            while ($i < $this->emailNumbers) {
                $this->attachements[$i] = $this->fetchContent($i + 1, $section);
                $i++;
            }
        }
        return $this->attachements;
    }

    public function removeEmail()
    {
        imap_expunge($this->allEmail);
        imap_close($this->allEmail);
    }
}