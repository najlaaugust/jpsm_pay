<?php

class dbguy {

	private $serverName;
	private $connectionInfo;

	public function __construct($serverName,$uid,$pwd,$database) {
		$this->serverName = $serverName;
		$this->connectionInfo = array("UID"=>$uid, "PWD"=>$pwd, "Database"=>$database, "CharacterSet" => "UTF-8");
	}
	
	private function insertData_GetId($theQuery, array $params, $logger)
	{
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo);
		if( $conn === false )
		{
			echo "Unable to connect.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$stmt = sqlsrv_query( $conn, $theQuery, $params);
		if( $stmt === false ) {
			die( print_r( sqlsrv_errors(), true));
		}
		
		//sqlsrv_next_result($stmt);
		sqlsrv_fetch($stmt);
		$id_to_return = sqlsrv_get_field($stmt, 0);		
	
		$logger->addInfo($id_to_return);
		
		sqlsrv_free_stmt( $stmt );
		sqlsrv_close( $conn);
		
		return $id_to_return;
	}
	

	private function insertData($theQuery, array $params)
	{
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo);
		if( $conn === false )
		{
			echo "Unable to connect.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$stmt = sqlsrv_query( $conn, $theQuery, $params);
		if( $stmt === false ) {
			die( print_r( sqlsrv_errors(), true));
		}
	
		sqlsrv_free_stmt( $stmt );
		sqlsrv_close( $conn);
	}
			
	
	private function updateData($theQuery, array $params)
	{
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo);
		if( $conn === false )
		{
			echo "Unable to connect.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$reslt = sqlsrv_query($conn, $theQuery, $params);
		if($reslt === false )
			die( FormatErrors( sqlsrv_errors() ) );
	
			sqlsrv_free_stmt( $reslt );
			sqlsrv_close( $conn );
	}	
	
	private function getData($theQuery, array $params, $logger)
	{
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo);
		if( $conn === false )
		{
			echo "Unable to connect.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$stmt = sqlsrv_query( $conn, $theQuery, $params);
		if( $stmt === false )
		{
			echo "Error in executing query.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$row = sqlsrv_fetch_array($stmt);
		//$logger->addInfo(print_r($row));
		sqlsrv_free_stmt( $stmt);
		sqlsrv_close( $conn);
	
		return $row;
	}
	
	private function getDataset($theQuery)
	{
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo);
		if( $conn === false )
		{
			echo "Unable to connect.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$stmt = sqlsrv_query( $conn, $theQuery);
		if( $stmt === false )
		{
			echo "Error in executing query.</br>";
			die( print_r( sqlsrv_errors(), true));
		}
	
		$result_array = array();
	
		while( $array = sqlsrv_fetch_array( $stmt)) {
			//print_r($array);
			$result_array[] = $array;
		}
		
		//print_r ($result_array);
	//echo (json_encode($result_array));
	
	/*
	 $json = json_encode($result_array, JSON_FORCE_OBJECT);
	 if ($json === false) {
	 // Avoid echo of empty string (which is invalid JSON), and
	 // JSONify the error message instead:
	 $json = json_encode(array("jsonError", json_last_error_msg()));
	 if ($json === false) {
	 // This should not happen, but we go all the way now:
	 $json = '{"jsonError": "unknown"}';
	 }
	 // Set HTTP response status code to: 500 - Internal Server Error
	 http_response_code(500);
	 }
	 echo $json;
	 */	
	
	
		sqlsrv_free_stmt( $stmt);
		sqlsrv_close( $conn);
	
		return $result_array;
	}
		
	
	public function getUserInfo($ConfirmNum, $LastName, $logger) {
		$tsql = "	SELECT 	c.*, r.courseid, r.payamount, r.paymethod, r.fname, r.lname, r.mname,
								r.confirm_num, r.cancelled_flag, r.notes
						FROM 	tbl_web_registrations AS r INNER JOIN tbl_courses AS c ON
								r.courseid = c.courseid
						WHERE 	r.confirm_num = ? AND 
								r.lname = ?";
				
		$row = $this->getData($tsql, array($ConfirmNum, $LastName), $logger);
		return $row;
	}
	
	public function getRegInfo($ConfirmNum, $logger) {
		$tsql = "	SELECT 	r.*, ft_name AS StudentType, a.aff_name AS AffiliationName, p.pm_name AS PaymentMethod, 
					    		ISNULL(wl.BypassWaitList, 1) AS BypassWaitList,	r.CourseID as TestCol
						FROM 	tbl_web_registrations AS r LEFT OUTER JOIN dbo.tbl_feeType as f ON 
								r.student_typ 	= f.ft_id LEFT OUTER JOIN dbo.tbl_affiliation AS a ON
								r.affiliation 	= a.aff_id LEFT OUTER JOIN dbo.tbl_PayMethod AS p ON
								r.paymethod 	= p.pm_id LEFT OUTER JOIN dbo.tbl_web_registrations_wl AS wl ON
					            r.RegID			= wl.RegID
						WHERE 	r.confirm_num 	= ?";
	
		$row = $this->getData($tsql, array($ConfirmNum), $logger);
		return $row;
	}
	
	public function getRegConfirmNum($RegId, $logger) {
		$tsql = "	SELECT 	confirm_num
							FROM 	tbl_web_registrations 
							WHERE 	RegID = ?";
							
		$row = $this->getData($tsql, array($RegId), $logger);
		return $row;
	}	
	
	public function getRegInfo_Web($ConfirmNum, $logger) {
		$tsql = "SELECT 	r.*, ft_name AS StudentType, a.aff_name AS AffiliationName, p.pm_name AS PaymentMethod, 
				    		ISNULL(wl.BypassWaitList, 1) AS BypassWaitList,	r.CourseID as TestCol
					FROM 	tbl_web_registrations AS r LEFT OUTER JOIN dbo.tbl_feeType as f ON 
							r.student_typ 	= f.ft_id LEFT OUTER JOIN dbo.tbl_affiliation AS a ON
							r.affiliation 	= a.aff_id LEFT OUTER JOIN dbo.tbl_PayMethod AS p ON
							r.paymethod 	= p.pm_id LEFT OUTER JOIN dbo.tbl_web_registrations_wl AS wl ON
				            r.RegID			= wl.RegID
					WHERE 	r.confirm_num 	= ?";
	
		$row = $this->getData($tsql, array($ConfirmNum), $logger);
		return $row;
	}	
	
	public function getRegInfo_ForPayment($RegID, $logger) {
		$tsql = "		SELECT 	r.*, c.CourseName, c.Short_Code AS ShortCode, 
				        		c.Cost_Full, c.Cost_Student, c.Cost_Affiliate,
								c.Cost_Full_EarlyDiscount, 
								c.Cost_Student_EarlyDiscount, 
								c.Cost_Affiliate_EarlyDiscount,
								c.EarlyDiscount, 
								c.EarlyDiscountDate
						FROM 	tbl_web_registrations AS r INNER JOIN tbl_courses AS c ON
								r.courseID = c.courseID
						WHERE 	RegID = ?";
	
		$row = $this->getData($tsql, array($RegID), $logger);
		return $row;
	}	
	
	public function getCourseInfo($CourseId, $logger) {
		$tsql = "	SELECT 	coursename, classdates, instructor_1, instructor_2, instructor_3, instructor_4,
								facility, location, deadline, cost_full, cost_student, stateid, cost_affiliate,
								payduedate, deadline_cancel_fullreimb, deadline_cancel_adminfee, deadline_cancel_noreimb,
								deadline_fellowship, notification_fellowship, webcourse, stateID, course_status,
								earlydiscount,
								earlydiscountdate,
								cost_full_earlydiscount, 
								cost_student_earlydiscount, 
								cost_affiliate_earlydiscount
						FROM 	tbl_courses 
						WHERE 	courseid = ?";
	
		$row = $this->getData($tsql, array($CourseId), $logger);
		return $row;
	}
	
	public function getCourseInfo_ForRegister($CourseId, $logger) {
		$tsql = "	SELECT 	coursename, classdates, instructor_1, instructor_2, instructor_3, instructor_4,
								facility, location, deadline, cost_full, cost_student, stateid, cost_affiliate,
								payduedate, deadline_cancel_fullreimb, deadline_cancel_adminfee, deadline_cancel_noreimb,
								deadline_fellowship, notification_fellowship, webcourse, stateID, course_status,
								earlydiscount,
								earlydiscountdate,
								cost_full_earlydiscount,
								cost_student_earlydiscount,
								cost_affiliate_earlydiscount
						FROM 	tbl_courses
						WHERE 	courseid = ? and allowWebRegistration = 1";
	
		$row = $this->getData($tsql, array($CourseId), $logger);
		return $row;
	}	
	
	public function getContactInfo($stateid, $logger) {
		$tsql = "	SELECT 	si.person, si.institution, si.address1, si.address2, si.city, si.stateid, si.zip,
								si.phone, si.fax, si.email, s.state_name
						FROM 	tbl_stateinfo AS si INNER JOIN dbo.tbl_state AS s ON
								si.stateid 	= s.state_id
						WHERE 	si.stateid	=  ?";
	
		$row = $this->getData($tsql, array($stateid), $logger);
		return $row;
	}	
	
	public function getPrefixes($logger) {
		$tsql = "	SELECT 	pt_name 
						FROM 	tbl_people_title 
						ORDER BY pt_name";
	
		$row = $this->getDataset($tsql);
		return $row;
	}	

	public function getAffiliates($logger) {
		$tsql = "	SELECT 	aff_id, aff_name 
					FROM 	tbl_affiliation 
					WHERE	aff_id <> 0
					ORDER BY aff_name";
	
		$row = $this->getDataset($tsql);
		return $row;
	}
	
	public function getStudentTypes($logger) {
		$tsql = "    SELECT 	ft_id, ft_name
					    FROM 	tbl_feetype
					    WHERE 	ft_id <> 'A'
					    ORDER BY ft_sort_order";
	
		$row = $this->getDataset($tsql);
		return $row;
	}
	
	public function getStates($logger) {
		$tsql = "	SELECT 	state_id, state_name
						FROM 	dbo.tbl_state 
						ORDER BY state_name";
	
		$row = $this->getDataset($tsql);
		return $row;
	}
	
	public function getCountries($logger) {
		$tsql = "	SELECT 	country_code, country_name
					FROM 	dbo.tbl_country 
					ORDER BY country_name ";
	
		$row = $this->getDataset($tsql);
		return $row;
	}	
	
	
	public function updateEarlyDiscount(array $data) {
	
		$tsql = "UPDATE 	tbl_web_registrations SET
					PayEarlyDiscount		= ?
					WHERE 	confirm_num		= ?";
	
		$this->updateData($tsql, $data);
	}	
	
	public function insertWebPayFlowLog(array $data) {
	
		$tsql = "INSERT INTO web_payflow_log (transaction_id, result_code, result_msg, transaction_amount, confirm_num, transaction_date)
					VALUES (?, ?, ?, ?, ?, ?)";
	
		$this->insertData($tsql, $data);
		 
	}
	
	public function insertRegistration(array $data, $logger) {
	
		$tsql = "SET NOCOUNT ON; INSERT INTO dbo.tbl_web_registrations (	
							entrydate,courseid,coursename,prefix,fname,mname,lname,agency,
							dept_room,street1,street2,city,state,
							zip,phone,ext,fax,email,payamount,paymethod,
							comments,presposlen,prestitle,presemplen,prevpos,
							edulevel,edulevelother,edufield,eduecon,
							webaccess1,webaccess2,webaccess3,
							student_typ,affiliation,country,province_state
						) VALUES (
							?,
							?,
							?,
							?,
							?,
				            ?,
							?,
							?,
							?,
							?,
							?,
							?,			
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?,
							?
						); SELECT SCOPE_IDENTITY();";	
		return $this->insertData_GetId($tsql, $data, $logger);		 
	}
		
	public function insertRegistration_WaitList(array $data) {
	
		$tsql = "INSERT INTO dbo.tbl_web_registrations_wl (RegID, confirm_num, BypassWaitList)
		            SELECT	RegID, confirm_num, 0
		            FROM	dbo.tbl_web_registrations 
		            WHERE	RegID = ?";
		return $this->insertData_GetId($tsql, $data);
	}
	
	
	public function updateWebApplications(array $data) {
	
		$tsql = "UPDATE 	tbl_web_registrations SET
					PayMethod		= ?,
					PayNumber		= ?,
					Paydate			= ?,
					payamount		= ?,				
					CCType			= ?,
					CCDate			= ?,
					CCName 			= ?,
					CCEmail 		= ?,
					CCPhone			= ?,
					CCAddress		= ?,
					updatereg_flag		= ?
					WHERE 	confirm_num		= ?";
	
		$this->updateData($tsql, $data);
	}	
}