<?php
class admission {

	public $core;
	public $view;
	public $item = NULL;

	public function configView() {
		$this->view->header = TRUE;
		$this->view->footer = TRUE;
		$this->view->menu = TRUE;
		$this->view->javascript = array();
		$this->view->css = array();

		return $this->view;
	}

	public function buildView($core) {
		$this->core = $core;
	}

	public function manageAdmission() {
		$this->activeAdmission();
		$this->rejectedAdmission();
	}

	public function profileAdmission($item) {
		if ($this->core->role < 100) {
			$sql = "SELECT * FROM `basic-information` as bi, `roles` as rl, `access` as ac WHERE ac.`ID` = '" . $this->core->username . "' AND ac.`ID` = bi.`ID` AND ac.`RoleID` = rl.`ID`";
		} else {
			$sql = "SELECT * FROM `basic-information` as bi, `roles` as rl, `access` as ac WHERE ac.`ID` = '" . $this->core->item . "' AND ac.`ID` = bi.`ID` AND ac.`RoleID` = rl.`ID`";
		}

		$this->showInfoProfile($sql);
	}

	public function completeAdmission($item) {
		$sql = "UPDATE `access` SET `RoleID` = 10 WHERE `access`.`ID` = '" . $item . "'";
		$run = $this->core->database->doInsertQuery($sql);

		$sql = "UPDATE `basic-information` SET `Status` = 'Enrolled' WHERE `basic-information`.`ID` = '" . $item . "'";
		$run = $this->core->database->doInsertQuery($sql);

		$sql = "SELECT * FROM `basic-information` WHERE `GovernmentID` = '" . $item . "'";
		$run = $this->core->database->doSelectQuery($sql);

		while ($fetch = $run->fetch_assoc()) {
			$recipient = $fetch["PrivateEmail"];
			$mailer = serviceBuilder("mailer");
			$mailer->newMail("registrationSuccessful", $recipient);
		}

		$this->core->redirect("admission", "manage", NULL);
	}

	public function promoteAdmission($item) {
		$sql = "UPDATE `edurole`.`access` SET `RoleID` = `RoleID`+1 WHERE `access`.`ID` = '" . $item . "' AND `RoleID` = '". $this->core->route[3]."'";
		$run = $this->core->database->doInsertQuery($sql);

		$this->core->redirect("admission", "manage", NULL);
	}

	public function rejectAdmission($item) {
		$sql = "UPDATE `edurole`.`basic-information` SET `Status` = 'Rejected' WHERE `basic-information`.`ID` = '" . $item . "';";
		$run = $this->core->database->doInsertQuery($sql);

		$this->core->redirect("admission", "manage", NULL);
	}

	public function continueAdmission($item) {
		$sql = "UPDATE `edurole`.`basic-information` SET `Status` = 'Requesting' WHERE `basic-information`.`ID` = '" . $item . "';";
		$run = $this->core->database->doInsertQuery($sql);

		$this->core->redirect("admission", "manage", NULL);
	}


	public function deleteAdmission($item) {
		$sql = "UPDATE `edurole`.`basic-information` SET `Status` = 'Failed' WHERE `basic-information`.`ID` = '" . $item . "';";
		$run = $this->core->database->doInsertQuery($sql);

		$this->core->redirect("admission", "manage", NULL);
	}

	public function activeAdmission() {
		echo '<p class="title1">Currently active admission requests</p> ';

		$sql = "SELECT * FROM `basic-information`
			LEFT JOIN `access` ON  `access`.ID = `basic-information`.ID
			LEFT JOIN `student-study-link` ON `basic-information`.`ID` = `student-study-link`.`StudentID`
			LEFT JOIN `study` ON `student-study-link`.`StudyID` = `study`.`ID` 
			WHERE `basic-information`.`Status` = 'Requesting'";

		$run = $this->core->database->doSelectQuery($sql);

		$sql = "SELECT Name, Value FROM `settings` WHERE `Name` LIKE 'AdmissionLevel%' ORDER BY Name ASC";
		$go = $this->core->database->doSelectQuery($sql);

		$i=1;
		while ($fetch = $go->fetch_row()) {
			$name = substr($fetch[1],0,16).'...';
			$statusName[$i] = $name;
			$i++;
		}
		
		echo '<table width="768" height="" border="0" cellpadding="5" cellspacing="0">
		<tr>
		<td bgcolor="#EEEEEE"></td>
		<td bgcolor="#EEEEEE" width="180px"><b> Student Name</b></td>
		<td bgcolor="#EEEEEE"><b> <b>National ID</b></td>
		<td bgcolor="#EEEEEE"><b> Admission Phase</b></td>
		<td bgcolor="#EEEEEE"><b> Study</b></td>
		<td bgcolor="#EEEEEE" width="90px"><b> Options</b></td>
		<td bgcolor="#EEEEEE" width="60px"></td>
		</tr>';

		while ($fetch = $run->fetch_row()) {

			$status = $fetch[7];
			$study = $fetch[36];
			$firstname = $fetch[0];
			$middlename = $fetch[1];
			$surname = $fetch[2];
			$sex = $fetch[3];
			$uid = $fetch[4];
			$nrc = $fetch[5];
			$role = $fetch[25];
			$status = $fetch[23];
		
			if ($status == 6) {
				$next = '<a href="' . $this->core->conf['conf']['path'] . '/admission/complete/' . $uid . '/' . $status . '"><img src="' . $this->core->fullTemplatePath . '/images/exleft.gif"> <b>Complete</b> </a>';
			} else {
				$next = '<a href="' . $this->core->conf['conf']['path'] . '/admission/promote/' . $uid . '/' . $status . '"><img src="' . $this->core->fullTemplatePath . '/images/exleft.gif"> Approve step </a>';
			}

			echo '<tr>
				<td><img src="' . $this->core->fullTemplatePath . '/images/user.png"></td>
				<td><a href="' . $this->core->conf['conf']['path'] . '/information/show/' . $uid . '"><b>' . $firstname . ' ' . $middlename . ' ' . $surname . '</b></a></td>
		
				<td>' . $nrc . '</td>
				<td><a href="' . $this->core->conf['conf']['path'] . '/admission/profile/' . $uid . '">' . $status .' - '.$statusName[$status].'  </a> </td>
				<td><b>' . $study . '</b></td>
				<td>' . $next . ' </td>
				<td> <a href="' . $this->core->conf['conf']['path'] . '/admission/reject/' . $uid . '"><img src="' . $this->core->fullTemplatePath . '/images/delete.gif"> Deny</a></td>
				</tr>';
		}

		echo '</table>';

	}

	public function rejectedAdmission() {

		echo '<p class="title1">Currently denied admission requests</p> ';

		$sql = "SELECT * FROM `basic-information`, `access`, `student-study-link`, `study` 
			WHERE `access`.`ID` = `basic-information`.`ID` 
			AND  `access`.`RoleID` < 10 
			AND  `access`.`ID` = `student-study-link`.`StudentID` 
			AND `student-study-link`.`StudyID` = `study`.ID 
			AND `basic-information`.Status = 'Rejected'";

		$run = $this->core->database->doSelectQuery($sql);

		$sql = "SELECT Name, Value FROM `settings` WHERE `Name` LIKE 'AdmissionLevel%' ORDER BY Name ASC";
		$go = $this->core->database->doSelectQuery($sql);
		
		$i=1;
		while ($fetch = $go->fetch_row()) {
			$name = substr($fetch[1],0,16).'...';
			$statusName[$i] = $name;
			$i++;
		}
		
		echo '<table width="768" height="" border="0" cellpadding="5" cellspacing="0">
		<tr>
		<td bgcolor="#EEEEEE"></td>
		<td bgcolor="#EEEEEE" width="180px"><b> Student Name</b></td>
		<td bgcolor="#EEEEEE"><b> <b>National ID</b></td>
		<td bgcolor="#EEEEEE"><b> Admission Phase</b></td>		
		<td bgcolor="#EEEEEE"><b> Study</b></td>		
		<td bgcolor="#EEEEEE" width="90px"><b> Options</b></td>
		<td bgcolor="#EEEEEE" width="60px"></td>
		</tr>';

		while ($fetch = $run->fetch_row()) {

			$status = $fetch[7];
			$study = $fetch[36];
			$firstname = $fetch[0];
			$middlename = $fetch[1];
			$surname = $fetch[2];
			$sex = $fetch[3];
			$uid = $fetch[4];
			$nrc = $fetch[5];
			$role = $fetch[25];
			$status = $fetch[23];

			echo '<tr>
			<td><img src="' . $this->core->fullTemplatePath . '/images/user.png"></td>
			<td><a href="' . $this->core->conf['conf']['path'] . '/information/show/' . $uid . '"><b>' . $firstname . ' ' . $middlename . ' ' . $surname . '</b></a></td>
			<td>' . $nrc . '</td>
				<td><a href="' . $this->core->conf['conf']['path'] . '/admission/profile/' . $uid . '">' . $status .' - '.$statusName[$status].'  </a> </td>
			<td><b>' . $study . '</b></td>
			<td><a href="' . $this->core->conf['conf']['path'] . '/admission/continue/' . $uid . '"><img src="' . $this->core->fullTemplatePath . '/images/edit.gif"> Continue </a></td>
			<td><a href="' . $this->core->conf['conf']['path'] . '/admission/delete/' . $uid . '"><img src="' . $this->core->fullTemplatePath . '/images/delete.gif"> Delete</a></td>
			</tr>';

		}

		echo '</table>';

	}

	private function showInfoProfile($sql) {

		$run = $this->core->database->doSelectQuery($sql);

		while ($fetch = $run->fetch_row()) {

			$id = $_SESSION['userid'];
			$firstname = $fetch[0];
			$middlename = $fetch[1];
			$surname = $fetch[2];
			$sex = $fetch[3];
			$studentid = $fetch[4];
			$nrc = $fetch[5];
			$studytype = $fetch[22];
			$role = $fetch[21];
			$status = $fetch[20];

			echo '<div class="studentname">' . $firstname . ' ' . $middlename . ' ' . $surname . ' </div>
			<table width="768" border="0" cellpadding="0" cellspacing="0">
			 <tr>
			<td>Student number</td>
			<td>' . $studentid . '</td>
			 </tr>
			</table>';

			$i = 1;

			echo '<p><b>To successfully complete your admission process you will need to complete ALL steps.</b></p>
			<table width="768" border="0" cellpadding="3" cellspacing="0">';

			$sql = "SELECT `Name`, `Value` FROM `settings` WHERE `Name` LIKE 'AdmissionLevel%'";
			$run = $this->core->database->doSelectQuery($sql);

			while ($fetch = $run->fetch_row()) {

				if ($i == $role && $status == "Rejected") {
					$background = 'style="background-color: #F2BFBF"';
					$step = '<image src="' . $this->core->fullTemplatePath . '/images/error.png"> <b>FAILED TO MEET REQUIREMENTS</b>';
				} elseif ($i <= $role) {
					$background = 'style="background-color: #DFF2BF"';
					$step = '<image src="' . $this->core->fullTemplatePath . '/images/check.png"> completed';
				} elseif ($i > $role) {
					$background = 'style="background-color: #fff"';
					$step = '<image src="' . $this->core->fullTemplatePath . '/images/tviload.gif"> not yet completed';
				}

				echo '<tr ' . $background . '>
					<td width="100"><b>Step ' . $i . '</b></td>
					<td width="300"><em>' . $fetch[1] . '</em></td>
					<td>' . $step . '</td>
					</tr>';

				$i++;
			}

			echo '</table>';

		}


	}
}

?>
