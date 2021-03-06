<?php
class assignments {

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
		include $this->core->conf['conf']['classPath'] . "files.inc.php";
	}

	function showAssignments() {

		$sql = "SELECT * FROM `assignments` LEFT JOIN `courses` ON `assignments`.CourseID = `courses`.ID  LEFT JOIN `basic-information` ON `basic-information`.ID = `assignments`.CreatorID ORDER BY DateCreated";
		$run = $this->core->database->doSelectQuery($sql);

		$init = TRUE;

		while ($fetch = $run->fetch_row()) {

			$grade = $fetch[3];
			$firstname = $fetch[17];
			$lastname = $fetch[19];
			$studentno = $fetch[8];
			$assignmentid = $fetch[0];
			$assignmentname = $fetch[2];
			$assignmentfile = $fetch[9];
			$batchdescription = $fetch[3];
			$uid = $fetch[21];
			$date = $fetch[11];


			echo '<div style="border:solid 1px #ccc; padding-left: 10px; margin-bottom: 4px;">
				<table width="756">' .
				'<tr>' .
				'<td width="200px"><b>Assignment name:</b></td>' .
				'<td width="150px"><b>Assigned by</b></td>' .
				'<td width="200px"><b>Deadline for submission</b></td>' .
				'<td width="100px"><b></td>' .
				'</tr>';
			echo '<tr>' .
				'<td><a href="' . $this->core->conf['conf']['path'] . '/courses/show/' . $assignmentid . '"><b>' . $assignmentname . '</b></a></td>' .
				'<td><a href="' . $this->core->conf['conf']['path'] . '/information/show/' . $uid . '">' . $firstname . ' ' . $lastname . '</a></td>' .
				'<td>' . $date . '</td>' .
				'<td>
				<b><a href="' . $this->core->conf['conf']['path'] . '/studies/edit/' . $fetch[0] . '"> Submit result </a></b>
				</td>' .
				'</tr></table></div>';


		}

		echo '</table></div></p>';

	}

	function manageAssignments($item) {

		$sql = "SELECT * FROM `assignments` LEFT JOIN `courses` ON `assignments`.CourseID = `courses`.ID  LEFT JOIN `basic-information` ON `basic-information`.ID = `assignments`.CreatorID ORDER BY DateCreated";
		$run = $this->core->database->doSelectQuery($sql);

		$init = TRUE;

		while ($fetch = $run->fetch_row()) {

			$grade = $fetch[3];
			$firstname = $fetch[16];
			$lastname = $fetch[18];
			$studentno = $fetch[8];
			$assignmentid = $fetch[0];
			$assignmentname = $fetch[2];
			$assignmentfile = $fetch[9];
			$batchdescription = $fetch[3];
			$uid = $fetch[20];
			$date = $fetch[11];


			echo '<div style="border:solid 1px #ccc; padding-left: 10px; margin-bottom: 4px;">
                            <table width="756">' .
				'<tr>' .
				'<td width="200px"><b>Assignment name:</b></td>' .
				'<td width="150px"><b>Assigned by</b></td>' .
				'<td width="200px"><b>Deadline for submission</b></td>' .
				'<td width="100px"><b></td>' .
				'</tr>';

			echo '<tr>' .
				'<td><a href="' . $this->core->conf['conf']['path'] . '/assignments/view/' . $assignmentid . '"><b>' . $assignmentname . '</b></a></td>' .
				'<td><a href="' . $this->core->conf['conf']['path'] . '/information/view/' . $uid . '">' . $firstname . ' ' . $lastname . '</a></td>' .
				'<td>' . $date . '</td>' .
				'<td>
				<b><a href="' . $this->core->conf['conf']['path'] . '/assignments/submit/' . $fetch[0] . '"> Submit result </a></b>
                            </td>' .
				'</tr></table></div>';


		}

	}
}

?>
