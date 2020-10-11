<?php
namespace tsframe\module;

use tsframe\exception\BaseException;

/**
 * Класс для получения почты по IMAP
 */
class MailReceiver {
    public static $IMAPServers = [
        'gmail.com' => 'imap.gmail.com',
        'googlemail.com' => 'imap.gmail.com',
        'onet.pl' => 'imap.poczta.onet.pl',
        'mail.ru' => 'map.mail.ru',
        'inbox.ru' => 'map.mail.ru',
        'list.ru' => 'map.mail.ru',
        'bk.ru' => 'map.mail.ru',
        'ya.ru' => 'imap.yandex.ru',
        'yandex.ru' => 'imap.yandex.ru',
        'yandex.by' => 'imap.yandex.ru',
        'yandex.ua' => 'imap.yandex.ru',
        'yandex.com' => 'imap.yandex.ru',
        'yandex.kz' => 'imap.yandex.ru',
    ];

    public static function detectIMAPServer(string $mail): ?string {
        $exp = explode('@', $mail);
        $domain = end($exp);

        return self::$IMAPServers[$domain] ?? null;
    }

	protected $connection, $login, $password, $imapServer, $imapPort;

	public function __construct(string $login, string $password, string $imapServer, int $imapPort = 993){
		if (!function_exists('imap_open')){
			throw new BaseException('IMAP extension does not configurated');
        }

        $this->login = $login;
        $this->password = $password;
        $this->imapServer = $imapServer;
        $this->imapPort = $imapPort;
	}

    /**
     * Подключиться к IMAP-серверу
     * @return bool
     * @throws BaseException
     */
	public function connect(): bool {
        $this->connection = imap_open('{'.$this->imapServer.':'.$this->imapPort.'/imap/ssl}INBOX', $this->login, $this->password);
        if(!$this->connection){
        	throw new BaseException('IMAP connection error: ' . imap_last_error(), 0, [
        		'server' => $this->imapServer,
        		'port' => $this->imapPort,
        		'login' => $this->login,
	        	'password' => $this->password,
        	]);
        }

        return true;
	}

	public function close(){
		imap_close($this->connection);
	}

	/**
	 * Чтение писем с сервера
	 * @param string $filter Фильтр сообщений
	 * 						ALL - все письма
	 * 						FROM "mail@mail.com" - письма от конкретного отправителя
	 */
	public function getInput(string $filter = 'ALL'): array {
		$this->connect();
		$emailData = imap_search($this->connection, $filter);
        $mails = [];

        foreach ($emailData as $emailIdent) {
            $overview = imap_fetch_overview($this->connection, $emailIdent, 0);
            $message = imap_fetchbody($this->connection, $emailIdent, 1);

            $id = isset($overview->message_id) ? $overview->message_id : $emailIdent;
        	$mails[$emailIdent] = [
        		'uid' => $overview[0]->uid,
        		'from' => $overview[0]->from,
        		'to' => $overview[0]->to,
        		'subject' => $overview[0]->subject,
        		'date' => date("d F, Y", strtotime($overview[0]->date)),
        		'ts' => strtotime($overview[0]->date),
        		'message' => trim(quoted_printable_decode($message)),
        		'message_original' => $message,
        	];
        }
        $this->close();

        return $mails;
	}
}