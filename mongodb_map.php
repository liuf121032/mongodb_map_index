<?php
/*
by:Fireflyi
time:2015-8-8
ci框架下的一个mogo  demo  只是帮助初学者学习认识mongo和mongo2d地理位置索引的使用左右
*/

class mongodb{

	public function index()
	{
		$user = $this->mongodb('ci','user');
		$data['user'] = $user->find()->fields(array("_id"=>true,"ip"=>true,"name"=>true,"address"=>true,"sim_address"=>true,"province"=>true,"city"=>true,"city_code"=>true,"district"=>true,"street"=>true,"street_number"=>true,"point"=>true));
		$this->load->view('Admin/admin',$data);
	}
	//插入
	public function insert(){
		$user = $this->mongodb('ci','user');
		$ip = $_POST['ip'];
		$name = $_POST['name'];
		$user_row = $this->getip($ip);
		//这里需要注意的是mongo的地理位置索引存的必须是int 或者浮点型数据，而$user_row['point']是字符串类型所以必须转换才可以存进去
		$x = floatval($user_row['content']['point']['x']);$y=floatval($user_row['content']['point']['y']);
		$row = array('ip'=>$ip,'name'=>$name,'address'=>$user_row['address'],"sim_address"=>$user_row['content']['address'],"province"=>$user_row['content']['address_detail']['province'],"city"=>$user_row['content']['address_detail']['city'],"city_code"=>$user_row['content']['address_detail']['city_code'],"district"=>$user_row['content']['address_detail']['district'],"street"=>$user_row['content']['address_detail']['street'],"street_number"=>$user_row['content']['address_detail']['street_number'],"point"=>[$x,$y]); // "point"=>array($x,$y);也可以
		$res=$user->insert($row);
		//$user->remove($row);
		if($res=="1"){
			echo 1;
		}else{echo 2;}
	}
	public function dele(){
		$user = $this->mongodb('ci','user');
		$_id = $_POST['_id'];
		//根据id删除需要先实例
		$_id = new MongoId($_id);
		$row = array("_id"=>(object)$_id);
		if($user->remove($row)){
			echo 1;
		}else{
			echo 2;
		}
	}
	public function update(){
		$_id = $_POST['_id'];
		$name = $_POST['name'];
		$user = $this->mongodb('ci','user');
		$_id = new MongoId($_id);
		$where = array("_id"=>(object)$_id);
		$newdata = array("name"=>$name);
		$user->update($where,array('$set'=>$newdata));
		echo 1;
	}
	//2D查找
	public function find(){
		$user = $this->mongodb('ci','user');
		$data['finds'] = $user->find(array('point'=>array('$near'=>[2,3])));
		
		$this->load->view('find',$data);
	}
	//这是百度的根据ip返回的api接口ak为自己的秘钥  coor=bd09ll返回经纬度
	public function getip($ip){
		$url ="http://api.map.baidu.com/location/ip?ak=uQNF0cTlKNdLaFEGDUfh8PET&ip={$ip}&coor=bd09ll";
		//初始化
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE); //获取数据(html)返回  且不显示页面
		$data = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($data,true);
		//$data = mb_convert_encoding($data, "utf-8", "gb2312");
		return $data;

	}
	//iconv("UTF-8", "GB2312//IGNORE", $data) 
	/*{  //百度api返回的数据结构
		address: "CN|北京|北京|None|CHINANET|1|None",   #地址  
		content:       #详细内容  
		{  
		address: "北京市",   #简要地址  
		address_detail:      #详细地址信息  
		{  
		city: "北京市",        #城市  
		city_code: 131,       #百度城市代码  
		district: "",           #区县  
		province: "北京市",   #省份  
		street: "",            #街道  
		street_number: ""    #门址  
		},  
		point:               #百度经纬度坐标值  
		{  
		x: "116.39564504",  
		y: "39.92998578"  
		}  
		},  
		status: 0     #返回状态码  
	}  *///d:\mongodb\bin\mongod.exe --dbpath=d:\mongodb\mongod\data\db
	 function mongodb($db,$biao){
		try{
			$m = new Mongo("192.168.140.81:27017");//填写自己的
			if(!$m){
				throw new Exception();
			}
		}
		catch(Exception $e){
			echo  $this->error =$e->getMessage();exit();
		}
		$db =$m->$db ;//选择数据库
		$b = $db->$biao;//选择表
		return $b;
	}

	
}
