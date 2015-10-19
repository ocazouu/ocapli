<?php

Class UnitTestFastReq extends UnitTestTool
{
	function run_test_first()
	{
		$first = UnitTest::first();

		return Array(
			"is_true" => ($first["config"] === 'home'),
			"final_return" => Array($first)
		);
	}

	function run_test_limit_0_first()
	{
		$first = UnitTest::limit(0)->first();

		return Array(
			"is_true" => ($first === false),
			"final_return" => $first
		);
	}

	function run_test_limit_1_first()
	{
		$first = UnitTest::limit(1)->first();

		return Array(
			"is_true" => ($first["id"] == 7),
			"final_return" => Array($first)
		);
	}

	function run_test_limit_1_last()
	{
		$last = UnitTest::limit(1)->last();

		return Array(
			"is_true" => ($last["id"] == 7),
			"final_return" => Array($last)
		);
	}

	function run_test_limit_0_last()
	{
		$last = UnitTest::limit(0)->last();

		return Array(
			"is_true" => ($last === false),
			"final_return" => $last
		);
	}

	function run_test_first_and_all()
	{
		$UnitTest = UnitTest::where(1);

		$first = $UnitTest->first();
		$all = $UnitTest->all();

		return Array(
			"is_true" => ($first["config"] === 'home' && count($all) === 19),
			"final_return" => Array($all)
		);
	}

	function run_test_all_and_first()
	{
		$UnitTest = UnitTest::where(1);

		$all = $UnitTest->all();
		$first = $UnitTest->first();

		return Array(
			"is_true" => ($first["config"] === 'home' && count($all) === 19),
			"final_return" => Array($first)
		);
	}

	function run_test_order_desc_id_last()
	{
		$last = UnitTest::order("id DESC")->last();

		return Array(
			"is_true" => ($last["id"] == 7),
			"final_return" => Array($last)
		);
	}

	function run_test_order_hard_first()
	{
		$first = UnitTest::order("created_at DESC")->order(["config DESC","version ASC"])->order(["created_at DESC","id ASC"])->first();

		return Array(
			"is_true" => ($first["id"] == 29),
			"final_return" => Array($first)
		);
	}

	function run_test_all()
	{
		$all = UnitTest::all();

		return Array(
			"is_true" => (count($all) === 19),
			"final_return" => $all
		);
	}

	function run_test_id_record_is_0_count_all()
	{
		$where = UnitTest::where(["id_record"=>0]);
		
		$count = $where->count();
		$all = $where->all();

		return Array(
			"is_true" => (count($all) === 17 && $count == 17),
			"final_return" => $count
		);
	}

	function run_test_count()
	{
		$count = UnitTest::count();

		return Array(
			"is_true" => ($count == 19),
			"final_return" => $count
		);
	}

	function run_test_limit_2_3_and_all()
	{
		$limit = UnitTest::limit(2,3)->all();

		return Array(
			"is_true" => (count($limit) === 3 && $limit[0]["id"] == 9),
			"final_return" => $limit
		);
	}

	function run_test_limit_2_3_and_first()
	{
		$limit = UnitTest::limit(2,3)->first();

		return Array(
			"is_true" => (array_key_exists('id', $limit) && $limit["id"] == 9),
			"final_return" => Array($limit)
		);
	}

	function run_test_limit_2_3_and_last()
	{
		$limit = UnitTest::limit(2,3)->last();

		return Array(
			"is_true" => (array_key_exists('id', $limit) && $limit["id"] == 11),
			"final_return" => Array($limit)
		);
	}

	function run_test_where_id_record_is_0_limit_2_2()
	{
		$where = UnitTest::where(["id_record" => 0])->limit(2,2)->all();

		return Array(
			"is_true" => (count($where) === 2 && $where[0]["id"] == 9),
			"final_return" => Array($where)
		);
	}

	function run_test_where_version_sup_7_limit_1_all()
	{
		$where = UnitTest::where(["version >" => 7])->limit(1)->all();

		return Array(
			"is_true" => (count($where) === 1 && $where[0]["id"] == 7),
			"final_return" => Array($where)
		);
	}

	function run_test_where_version_sup_7_all()
	{
		$where = UnitTest::where(["version >" => 7])->all();

		return Array(
			"is_true" => (count($where) === 6 && $where[0]["id"] == 7),
			"final_return" => $where
		);
	}

	function run_test_where_version_sup_7_last()
	{
		$where = UnitTest::where(["version >" => 7])->last();

		return Array(
			"is_true" => ($where["id"] == 12),
			"final_return" => array($where)
		);
	}

	function run_test_where_hard_first()
	{
		$where = UnitTest::where("AND version = 0")->where(["OR version" => 1])->where(["OR version" => 2])->first();

		return Array(
			"is_true" => ($where["version"] == 2 && $where["id"]),
			"final_return" => array($where)
		);
	}

	function run_test_select_hard_last()
	{
		$where = UnitTest::select("id, config")->select("version AS v")->select(["id","created_at"])->last();

		return Array(
			"is_true" => ($where["v"] == 0),
			"final_return" => array($where)
		);
	}

	function run_test_group_config()
	{
		$where = UnitTest::select("config, SUM(1) AS nb_records")->group("config")->limit(1)->all();

		return Array(
			"is_true" => ($where[0]["nb_records"] == 19),
			"final_return" => $where
		);
	}

	function run_test_create()
	{
		$create = UnitTest::create(["config"=>"home"]);

		return Array(
			"is_true" => $create === true,
			"final_return" => $create
		);
	}

	function run_test_create_and_use()
	{
		$create = UnitTest::create_and_use(["config"=>"home"]);

		return Array(
			"is_true" => ($create["id"] == 31),
			"final_return" => Array($create)
		);
	}

	function run_test_get_in_other_model()
	{
		$UnitTest = UnitTest::select("id, config")->first();
		$ConfLang  = ConfLang::first();

		$final_return = array_merge(Array($UnitTest), Array($ConfLang));

		return Array(
			"is_true" => $ConfLang["string_id"] == "2721819029",
			"final_return" => $final_return
		);
	}

	function run_test_continue_other_model()
	{
		$all = ConfLang::where(["eng" => "A traduire"])->all();

		return Array(
			"is_true" => count($all) == 42,
			"final_return" => $all
		);
	}

	function run_test_update()
	{
		$update = UnitTest::update(["description" => "coucou"]);
		$first  = UnitTest::first();

		return Array(
			"is_true" => $update && $first["description"] == "coucou",
			"final_return" => array($first)
		);
	}
}

?>