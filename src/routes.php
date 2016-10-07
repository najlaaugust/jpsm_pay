<?php


$app->post('/paypalcallback', function ($request, $response, $args) {

	$data = $request->getParsedBody();
	$PNREF = filter_var($data['PNREF'], FILTER_SANITIZE_STRING); //PayFlow transaction number -->
	$RESULT = filter_var($data['RESULT'], FILTER_SANITIZE_STRING);
	$RESPMSG = filter_var($data['RESPMSG'], FILTER_SANITIZE_STRING);
	$AMOUNT = filter_var($data['AMOUNT'], FILTER_SANITIZE_STRING);
	$CUSTID = filter_var($data['CUSTID'], FILTER_SANITIZE_STRING); //SI confirmation number -->
	$NAME = filter_var($data['NAME'], FILTER_SANITIZE_STRING);
	$EMAIL = filter_var($data['EMAIL'], FILTER_SANITIZE_STRING);
	$PHONE = filter_var($data['PHONE'], FILTER_SANITIZE_STRING);
	$ADDRESS = filter_var($data['ADDRESS'], FILTER_SANITIZE_STRING);
	$CITY = filter_var($data['CITY'], FILTER_SANITIZE_STRING);
	$STATE = filter_var($data['STATE'], FILTER_SANITIZE_STRING);
	$ZIP = filter_var($data['ZIP'], FILTER_SANITIZE_STRING);
	$COUNTRY = filter_var($data['COUNTRY'], FILTER_SANITIZE_STRING);

	$CADDRESS = sprintf('%1$s %2$s, %3$s %4$s %5$s', $ADDRESS, $CITY, $STATE, $ZIP, $COUNTRY);

	if 	($RESULT == "0" && $RESPMSG == "Approved") {
		//update registation with payment info from PayFlow, once payment has been posted --->

		$payment_data = [];
		$payment_data[] = "CH";
		$payment_data[] = $PNREF;
		$payment_data[] = new DateTime();
		$payment_data[] = $AMOUNT;
		
		$payment_data[] = "PayPal";
		$payment_data[] = new DateTime();
		$payment_data[] = $NAME;
		$payment_data[] = $EMAIL;
		$payment_data[] = $PHONE;
		
		$payment_data[] = sprintf('%1$s %2$s, %3$s %4$s %5$s', $ADDRESS, $CITY, $STATE, $ZIP, $COUNTRY);
		$payment_data[] = 1;

		$payment_data[] = $CUSTID;

		$this->dbguy->updateWebApplications($payment_data);

		//query reg info
		/*
		$regInfo = json_encode($this->dbguy->getRegInfo_ForEmail($CUSTID));

		if (empty($regInfo)) {
			die("died");
		}

		//query course info
		$courseInfo = json_encode($this->dbguy->getCourseInfo());

		$courseSignedUpFor = array();

		foreach ( $regInfo as $key => $value ) {
			foreach ( $courseInfo as $course ) {
				//is registered or not?
				if ($course->course_id == $key && $value == 1) {
					$courseSignedUpFor[] = $course;
				}
			}
		}

		$address = sprintf('%1$s %2$s %3$s', $regInfo->address1, $regInfo->address2, $regInfo->address3);

		//Send Email confirmation to SI --->
		$this->emailguy->sendEmail(
				$this->logger,
				$regInfo->CurrentYear, $regInfo->Confirm_Num, $PNREF,
				$regInfo->title, $regInfo->fname, $regInfo->mname, $regInfo->lname,
				$address, $regInfo->city, $regInfo->StateName, $regInfo->zip, $regInfo->CountryName, $regInfo->hphone, $regInfo->email,

				$NAME, $CADDRESS, $PHONE, $EMAIL, $AMOUNT,
				$courseSignedUpFor);
				*/
	}

	$this->logger->addInfo("Add payment record to database");

	$data = $request->getParsedBody();

	$payment_record = [];
	$payment_record[] = $PNREF;
	$payment_record[] = $RESULT;
	$payment_record[] = $RESPMSG;
	$payment_record[] = $AMOUNT;
	$payment_record[] = $CUSTID;
	$payment_record[] = new DateTime();

	$this->dbguy->insertWebPayFlowLog($payment_record);


	$this->logger->addInfo("done with all.");

	exit;
});


$app->get('/isvalid/{ConfirmNum}/{LastName}', function ($request, $response, $args) {
	$this->logger->addInfo("Check if exists in database");

	$ConfirmNum = $args['ConfirmNum'];
	$LastName = $args['LastName'];

	//query user info
	$userInfo = $this->dbguy->getUserInfo($ConfirmNum, $LastName, $this->logger);
	//print_r($userInfo);

	if (empty($userInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($userInfo);
	exit;
});


$app->get('/reginfo_forpayment/{RegID}', function ($request, $response, $args) {

	$RegID = $args['RegID'];

	//query reg info
	$regInfo = $this->dbguy->getRegInfo_ForPayment($RegID, $this->logger);
	//print_r($userInfo);

	if (empty($regInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($regInfo);
	exit;
});


$app->get('/reginfo/{ConfirmNum}', function ($request, $response, $args) {

	$ConfirmNum = $args['ConfirmNum'];

	//query reg info
	$regInfo = $this->dbguy->getRegInfo($ConfirmNum, $this->logger);
	//print_r($userInfo);

	if (empty($regInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($regInfo);
	exit;
});


$app->get('/reginfo_web/{ConfirmNum}', function ($request, $response, $args) {

	$ConfirmNum = $args['ConfirmNum'];

	//query reg info
	$regInfo = $this->dbguy->getRegInfo_Web($ConfirmNum, $this->logger);
	//print_r($userInfo);

	if (empty($regInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($regInfo);
	exit;
});

$app->get('/prefixes', function ($request, $response, $args) {

	$info = $this->dbguy->getPrefixes($this->logger);

	if (empty($info)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($info);
	exit;
});

$app->get('/affiliates', function ($request, $response, $args) {

	$info = $this->dbguy->getAffiliates($this->logger);

	if (empty($info)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($info);
	exit;
});

$app->get('/studenttypes', function ($request, $response, $args) {

	$info = $this->dbguy->getStudentTypes($this->logger);

	if (empty($info)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($info);
	exit;
});
	
$app->get('/states', function ($request, $response, $args) {

	$info = $this->dbguy->getStates($this->logger);

	if (empty($info)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($info);
	exit;
});

$app->get('/countries', function ($request, $response, $args) {

	$info = $this->dbguy->getCountries($this->logger);

	if (empty($info)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($info);
	exit;
});
	
	
$app->get('/courseinfo/{CourseId}', function ($request, $response, $args) {

	$CourseId = $args['CourseId'];
	$courseInfo = $this->dbguy->getCourseInfo($CourseId, $this->logger);
	//print_r($userInfo);

	if (empty($courseInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($courseInfo);
	exit;
});

$app->get('/courseinfo_forregister/{CourseId}', function ($request, $response, $args) {

	$CourseId = $args['CourseId'];
	$courseInfo = $this->dbguy->getCourseInfo_ForRegister($CourseId, $this->logger);
	//print_r($userInfo);

	if (empty($courseInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($courseInfo);
	exit;
});


$app->get('/contactinfo/{stateid}', function ($request, $response, $args) {

	$stateid = $args['stateid'];
	$contactInfo = $this->dbguy->getContactInfo($stateid, $this->logger);
	//print_r($userInfo);

	if (empty($contactInfo)) {
		header("Content-Type: application/json");
		echo json_encode(array('notfound' => 'notfound'));
		exit;
	}

	header("Content-Type: application/json");

	echo json_encode($contactInfo);
	exit;
});


$app->post('/payearly/update', function($request, $response) {
	$this->logger->addInfo("Update registration record in database");

	$data = $request->getParsedBody();

	$payment_data = [];
	$payment_data[] = filter_var($data['PayEarlyDiscount'], FILTER_SANITIZE_STRING);
	$payment_data[] = filter_var($data['confirm_num'], FILTER_SANITIZE_STRING);

	$this->dbguy->updateEarlyDiscount($payment_data);
});


$app->post('/registration/new', function($request, $response) {
	$this->logger->addInfo("insert registration record in database");

	$data = $request->getParsedBody();
	
	$theCourseId=filter_var($data['CourseID'], FILTER_SANITIZE_STRING);

	$mydata = [];
	$mydata[] = new DateTime();
	$mydata[] = $theCourseId;
	$mydata[] = filter_var($data['CourseName'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['Prefix'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['fname'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['mname'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['lname'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['agency'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['dept_room'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['street1'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['street2'], FILTER_SANITIZE_STRING);	
	$mydata[] = filter_var($data['city'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['state'], FILTER_SANITIZE_STRING);	
	$mydata[] = filter_var($data['zip'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['phone'], FILTER_SANITIZE_STRING);	
	$mydata[] = filter_var($data['ext'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['fax'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['email'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['payamount'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['paymethod'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['Comments'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['PresPosLen'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['PresTitle'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['PresEmpLen'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['PrevPos'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['EduLevel'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['EduLevelOther'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['EduField'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['EduEcon'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['WebAccess1'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['WebAccess2'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['WebAccess3'], FILTER_SANITIZE_STRING);
	
	$mydata[] = filter_var($data['studenttype'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['affiliation'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['country'], FILTER_SANITIZE_STRING);
	$mydata[] = filter_var($data['province'], FILTER_SANITIZE_STRING);
	
	$newId = $this->dbguy->insertRegistration($mydata, $this->logger);
	
	$courseInfo = json_encode($this->dbguy->getCourseInfo($theCourseId, $this->logger));
	
	if ($courseInfo->course_status == "WL") {
		$this->dbguy->insertRegistration_WaitList(array($newId));
	}
		
	$confirm_num = $this->dbguy->getRegConfirmNum(array($newId));
	echo $confirm_num["confirm_num"];
	exit;	

			
});