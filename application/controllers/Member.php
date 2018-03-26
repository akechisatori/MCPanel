<?php
class Member extends CI_Controller {
	// public function get($username = '', $token = '') {
	// 	$this->load->driver('cache');
	// 	$name = strtolower($username);
	// 	if ($this->cache->redis->get($name) !== $token) {
	// 		exit(json_encode([
	// 			'code' => 401,
	// 		]));
	// 	}
	// 	$user = $this->db->select("id,name,realname")->get_where("Member", ['name' => $name])->result()[0];
	// 	$balance = $this->db->select("username,balance")->get_where("Balance", ["username" => $name])->result()[0];
	// 	$user->id = intval($user->id);
	// 	$user->balance = $balance->balance;
	// 	$user->status = 200;
	// 	exit(json_encode($user));
	// }
	public function getSelf($token = '') {
		$this->load->driver('cache');

		if (!$name = $this->cache->redis->get($token)) {
			exit(json_encode([
				'code' => 401,
			]));
		}

		$user = $this->db->select("id,name,realname")->get_where("Member", ['name' => $name])->result()[0];
		$balance = $this->db->select("username,balance")->get_where("Balance", ["username" => $name])->result()[0];
		$user->id = intval($user->id);
		$user->balance = $balance->balance;
		$user->status = 200;
		exit(json_encode($user));
	}
	public function Login($username = '', $password = '') {
		$this->load->driver('cache');
		$name = strtolower($username);
		if (!$this->UserExists($name)) {
			exit(json_encode(['code' => 404]));
		}
		if ($this->GetUserData($name)->password !== hash("sha512", $password)) {
			exit(json_encode(['code' => 403]));
		}
		$token = md5(uniqid());
		if (!$rtoken = $this->cache->redis->get($name)) {
			$this->cache->redis->save($name, $token, 3600);
			$this->cache->redis->save($token, $name, 3600);
			exit(json_encode([
				'code' => 200,
				//'msg' => status::LOGIN_SUCCESS,
				'token' => $token,
			]));
		} else {
			$this->cache->redis->save($name, $rtoken, 3600);
			$this->cache->redis->save($rtoken, $name, 3600);
			exit(json_encode([
				'code' => 200,
				//'msg' => status::LOGIN_SUCCESS,
				'token' => $rtoken,
			]));
		}
	}
	private function UserExists($username = '') {
		if (@$this->db->select("id,name,realname")->get_where("Member", ['name' => $username])->result()[0]) {
			return true;
		} else {
			return false;
		}
	}
	private function GetUserData($username = '') {
		return $this->db->select("*")->get_where("Member", ['name' => $username])->result()[0];
	}
}