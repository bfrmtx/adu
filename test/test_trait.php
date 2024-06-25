<?php
//definition of trait
trait mytrait{
        public $dt;
    function __construct() {
    $this->dt = "empty";
    }

	public function test1(){
		echo "test1 method in trait1 " . $this->dt . "\n";
	}
}
class myclass{
	public function test2(){
		echo "test2 method in parent class\n";
	}
}

//using trait and parent class
class testclass extends myclass {
	use mytrait;
	public function test3(){
		echo "implementation of final class method\n";
		// $this->dt = "hello var!";
	}
}
$obj=new testclass();
$obj->test3();
$obj->test1();
$obj->test2();
