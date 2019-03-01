<?php

/**
 * Bitrix24 connector.
 *
 * @version     1.0.1
 * @author      Bitrix24
 * @copyright   2016 Bitrix24
 * @link        https://bitrix24.com
 */
class B24Connector
{
	private static $current = null;
	private $portal = null;
	private $channel = null;
	private $currency = null;

	/**
	 * Initialize vars, create/get connector.
	 */
	private function __construct()
	{
		$connId = B24Gate::getConnectorId();
		$this->portal = trim(B24Gate::getConfig('b24c_portal'));
		$this->channel = trim(B24Gate::getConfig('b24c_channel'));
		$this->currency = trim(B24Gate::getConfig('b24c_currency'));

		if (!$this->portal)
		{
			return;
		}
		if (!$this->currency)
		{
			$this->currency = 'USD';
		}

		//check current channel
		if ($this->channel)
		{
			$result = $this->postCommand('crm.externalchannel.connector.list', array(
				'filter' => array(
					'CHANNEL_ID' => $this->channel,
				)
			));
			if (!$result || !isset($result['result']) || empty($result['result']))
			{
				$this->channel = '';
				B24Gate::saveConfig('b24c_channel', $this->channel);
			}
		}

		//check other exists channel
		if (!$this->channel)
		{
			$result = $this->postCommand('crm.externalchannel.connector.list', array(
				'filter' => array(
					'ORIGINATOR_ID' => $connId,
				)
			));
			if ($result && isset($result['result']) && !empty($result['result']))
			{
				$this->channel = isset($result['result'][0]['CHANNEL_ID']) ? $result['result'][0]['CHANNEL_ID'] : '';
				B24Gate::saveConfig('b24c_channel', $this->channel);
			}
		}

		//create connector / save channel
		if (!$this->channel)
		{
			$result = $this->postCommand('crm.externalchannel.connector.register', array(
				'fields' => array(
					'NAME' => B24Gate::getConnectorName(),
					'TYPE' => $connId,
					'ORIGINATOR_ID' => $connId,
					'EXTERNAL_SERVER_HOST' => B24Gate::getConnectorHost()
				)
			));
			if ($result && isset($result['result']) && !empty($result['result']))
			{
				$this->channel = isset($result['result']['result']) ? $result['result']['result'] : '';
				B24Gate::saveConfig('b24c_channel', $this->channel);
			}
		}
	}

	/**
	 * Initialize connector.
	 * @return self
	 */
	public static function getCurrent()
	{
		if (self::$current === null)
		{
			self::$current = new self();
		}
		return self::$current;
	}

	/**
	 * Post some command.
	 * @param string $command rest command
	 * @param array $fields rest fields
	 * @return array
	 */
	private function postCommand($command, array $fields)
	{
		$return = array();
		if (function_exists('curl_init'))
		{
			$url = $this->portal . $command;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
			$response = curl_exec($ch);
			curl_close($ch);
			$return = json_decode($response, true);
			if (!$return)
			{
				$return = array();
			}
		}
		return $return;
	}

	/**
	 * Send activity.
	 * @param array $fields fields of activity
	 * @return array result
	 */
	public function sendActivity(array $fields)
	{
		if (!$this->channel)
		{
			return false;
		}
		if (!isset($fields['AGENT']))
		{
			$fields['AGENT'] = array();
		}
		if (!isset($fields['ACTIVITY']))
		{
			$fields['ACTIVITY'] = array();
		}
		if (!isset($fields['AGENT']['ORIGIN_ID']))
		{
			$fields['AGENT']['ORIGIN_ID'] = md5(serialize($fields));
		}
		if (isset($fields['AGENT']['PHONE']) && $fields['AGENT']['PHONE'] != '')
		{
			$fields['AGENT']['PHONE'] = array(
				'v1' => array(
					'VALUE' => $fields['AGENT']['PHONE'],
					'VALUE_TYPE' => 'WORK'
				)
			);
		}
		if (isset($fields['AGENT']['EMAIL']) && $this->check_email($fields['AGENT']['EMAIL']))
		{
			$fields['AGENT']['EMAIL'] = array(
				'v1' => array(
					'VALUE' => $fields['AGENT']['EMAIL'],
					'VALUE_TYPE' => 'WORK'
				)
			);
		}
		else
		{
			$fields['AGENT']['EMAIL'] = array();
		}
		if (!$fields['ACTIVITY']['RESULT_CURRENCY_ID'])
		{
			$fields['ACTIVITY']['RESULT_CURRENCY_ID'] = $this->currency;
		}

		$connId = B24Gate::getConnectorId();
		$fields['AGENT']['ORIGIN_VERSION'] = 'v1';
		$fields['AGENT']['ORIGIN_ID'] = $connId . '_' . $fields['AGENT']['ORIGIN_ID'];
		$fields['ACTIVITY']['ORIGIN_ID'] = $connId . '_' . $fields['ACTIVITY']['ORIGIN_ID'];
		$fields['ACTIVITY']['START_TIME'] = date('c');
		$fields['ACTIVITY']['SUBJECT'] .= ' #' . B24Gate::getConnectorName();
		$fields['ACTIVITY']['RESULT_VALUE'] = 1;

		$fields['ACTIVITY_EXT'] = array();
		$fields['ACTIVITY_EXT']['NUMBER'] = $fields['ACTIVITY']['NUMBER'];
		$fields['ACTIVITY_EXT']['EXTERNAL_URL'] = $fields['ACTIVITY']['EXTERNAL_URL'];
		unset($fields['ACTIVITY']['NUMBER'], $fields['ACTIVITY']['EXTERNAL_URL']);

		return $this->postCommand('crm.externalchannel.activity.contact', array(
			'batch' => array(array(
				'agent' => array(
					'fields' => $fields['AGENT'],
				),
				'activity' => array(
					'fields' => $fields['ACTIVITY'],
					'external_fields' => $fields['ACTIVITY_EXT']
				)
			)),
			'params' => array(
				'CHANNEL_ID' => $this->channel
			)
		));
	}

	/**
	 * Check email for correct.
	 * @param string $email
	 * @return boolean
	 */
	private function check_email($email)
	{
		$email = trim($email);

		if ($email == '')
		{
			return false;
		}

		if(preg_match("#.*?[<\\[\\(](.*?)[>\\]\\)].*#i", $email, $arr) && strlen($arr[1])>0)
		{
			$email = $arr[1];
		}

		//http://tools.ietf.org/html/rfc2821#section-4.5.3.1
		//4.5.3.1. Size limits and minimums
		if (strlen($email) > 320)
		{
			return false;
		}

		//http://tools.ietf.org/html/rfc2822#section-3.2.4
		//3.2.4. Atom
		static $atom = "=_0-9a-z+~'!\$&*^`|\\#%/?{}-";

		//"." can't be in the beginning or in the end of local-part
		//dot-atom-text = 1*atext *("." 1*atext)
		if (preg_match("#^[".$atom."]+(\\.[".$atom."]+)*@(([-0-9a-z_]+\\.)+)([a-z0-9-]{2,20})$#i", $email))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}