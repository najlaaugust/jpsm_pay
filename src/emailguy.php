<?php

class emailguy {
	
	private $smtp_server;
	private $smtp_user;
	private $smtp_pwd;
	private $smtp_from;
	
	public function __construct($smtp_server,$smtp_user,$smtp_pwd,$smtp_from) {
		$this->smtp_server = $smtp_server;
		$this->smtp_user = $smtp_user;
		$this->smtp_pwd = $smtp_pwd;
		$this->smtp_from = $smtp_from;
	}
	
	public function sendEmail($logger,
								$CurrentYear, $Confirm_Num, $PNREF,
								$title, $fname, $mname, $lname,
								$address, $city, $state, $zip, $country, 
								$phone, $email,								
								$CNAME, $CADDRESS, $CPHONE, $CEMAIL, $AMOUNT,
								$courseSignedUpFor) 
	{
		$logger->addInfo('sending email to.. ' . $CEMAIL);
		//compose message begin
		$full_name = sprintf('%1$s %2$s %3$s %4$s', $title, $fname, $mname, $lname);
		$sIntroText = sprintf('%1$s has made a payment of %2$s.', $full_name, $AMOUNT);		
		$sAmountText = sprintf('PayPal Transaction #: %1$s <br> Amount Paid: %2$s', $PNREF, $AMOUNT);
		
		$myCourses = '<ul>';
		foreach ( $courseSignedUpFor as $course ) {
			$myCourses = $myCourses . '<li>' . $course->course_full_name . ' (' . $course->schedule_date . ')</li>';
		}		
		$myCourses = $myCourses . '</ul>';
		
				
		//compose message end		
		$subject = sprintf('%1$s Summer Institute Course/Workshop | Confirmation #: %2$s | Applicant Name: %3$s', $CurrentYear, $Confirm_Num, $CNAME);
			
		$headers  = "From: " . $this->smtp_from . "\r\n";
		$headers .= "Reply-To: ". $this->smtp_from . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";		
		
		$mail = new PHPMailer;
		
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output
		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $this->smtp_server;  						// Specify main and backup SMTP servers
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		$mail->Username = $this->smtp_user;                 // SMTP username
		$mail->Password = $this->smtp_pwd;                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		//$mail->Port = 587;                                    // TCP port to connect to
		
		$mail->setFrom($this->smtp_from, $this->smtp_from);
		$mail->addAddress($CEMAIL, $full_name);     // Add a recipient
		$mail->addReplyTo($this->smtp_from, $this->smtp_from);

		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $subject;
		
		$mail->Body    = sprintf('
<html>
<body>				
<p>				
------------------------------------------
CONFIRMATION NUMBER: %1$s
------------------------------------------    
</p>
<p>				
%2$s
</p>
<br/><br/>
<p>
------------------------------------------
APPLICANT INFO:
------------------------------------------
</p>
<p>				
Name: %2$s <br>
Address: %4$s <br>
City: %5$s <br>
State: %6$s <br>
Zip: %7$s <br>
Country: %8$s <br>
</p>
<p>
Phone: %9$s
E-Mail: %10$s
</p>
<br/><br/>				
<p>
------------------------------------------
PAYEE INFO:
------------------------------------------
</p>
<p>				
Name: %11$s <br>
Address: %12$s <br>
</p>
<p>
Phone: %13$s <br>
Email: %14$s <br>
</p>
<br/><br/>				
<p>
------------------------------------------
FEES:
------------------------------------------
</p>
<p>				
%15$s
</p>
<br/><br/>				
<p>
------------------------------------------
COURSE/WORKSHOP:
------------------------------------------
</p>
<p>				
%16$s
</p>				
</body>				
</html>', $Confirm_Num, $sIntroText, $full_name, $address, $city, $state, $zip, $country, $phone, $email, $CNAME, $CADDRESS, $CPHONE, $CEMAIL, $sAmountText, $myCourses);
		
		
		$mail->AltBody = sprintf('
------------------------------------------
CONFIRMATION NUMBER: %1$s
------------------------------------------    
%2$s           
------------------------------------------
APPLICANT INFO:
------------------------------------------
Name: %3$s
Address: %4$s
City: %5$s
State: %6$s
Zip: %7$s
Country: %8$s

Phone: %9$s
E-Mail: %10$s
------------------------------------------
PAYEE INFO:
------------------------------------------
Name: %11$s
Address: %12$s

Phone: %13$s
Email: %14$s
------------------------------------------
FEES:
------------------------------------------
%15$s
------------------------------------------
COURSE/WORKSHOP:
------------------------------------------
%16$s', $Confirm_Num, $sIntroText, $full_name, $address, $city, $state, $zip, $country, $phone, $email, $CNAME, $CADDRESS, $CPHONE, $CEMAIL, $sAmountText, $myCourses);
		
		if(!$mail->send()) {
			$logger->addInfo('Message could not be sent.');
			$logger->addInfo($mail->ErrorInfo);
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			$logger->addInfo('Message has been sent.');
			echo 'Message has been sent';
		}
	}
}