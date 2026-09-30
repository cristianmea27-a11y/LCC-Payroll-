<?php
    $conn = mysqli_connect("localhost","root","","db_employee");
    if (!$conn) die("Connection failed: ".mysqli_connect_error());

    session_start();

    if (!isset($_SESSION["user_id"])) {
        header("Location: login.html");
        exit();
    }

    $department_edit_mode = false;
    $department = null;

    $position_edit_mode = false;
    $position = null;

    $employee_edit_mode = false;
    $employee = null;
    $employee_error = "";

    $open_main = "dashboard";
    $open_section = "";

    if (isset($_GET["search_employee"])) {
        $search = $_GET["search_employee"];

        $result = $conn->query("SELECT employee_no, first_name, last_name
                                FROM payroll_personal_info
                                WHERE employee_no LIKE '%$search%'
                                OR first_name LIKE '%$search%'
                                OR last_name LIKE '%$search%'
                                LIMIT 10");

        $employees = [];

        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($employees);
        exit;
    }

    if (isset($_POST["save_employee"])) {

        $employee_no = $_POST["employee_no"];
        $password = $_POST["employee_password"];
        $last_name = $_POST["employee_last_name"];
        $first_name = $_POST["employee_first_name"];
        $middle_name = $_POST["employee_middle_name"];
        $gender = $_POST["employee_gender"];
        $birth_date = $_POST["employee_birth_date"];
        $civil_status = $_POST["employee_civil_status"];
        $nationality = $_POST["employee_nationality"];
        $religion = $_POST["employee_religion"];
        $email = $_POST["employee_email"];
        $contact_no = $_POST["employee_contact"];
        $permanent_address = $_POST["employee_permanent_address"];
        $present_address = $_POST["employee_present_address"];
        $sss_no = $_POST["employee_sss"];
        $philhealth_no = $_POST["employee_philhealth"];
        $pagibig_no = $_POST["employee_pagibig"];
        $tin_no = $_POST["employee_tin"];
        $atm_no = $_POST["employee_atm"];
        $department_no = $_POST["employee_department"];
        $position_no = $_POST["employee_position"];
        $starting_day = $_POST["employee_starting_day"];
        $ending_day = $_POST["employee_ending_day"];
        $salary = $_POST["employee_salary"];
        $rate_type = $_POST["employee_rate_type"];

        if (!preg_match("/^[A-Za-z0-9-]+$/", $employee_no)) {
            echo "Employee Number may only contain letters, numbers, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $last_name)) {
            echo "Last Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $first_name)) {
            echo "First Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if ($middle_name != "" && !preg_match("/^[\p{L}\s.'-]+$/u", $middle_name)) {
            echo "Middle Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!in_array($gender, ["M", "F", "O"], true)) {
            echo "Invalid gender selected.";
        }
        else if (!in_array($civil_status, ["S", "M", "W", "LS"], true)) {
            echo "Invalid civil status selected.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $nationality)) {
            echo "Nationality may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $religion)) {
            echo "Religion may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Please enter a valid email address.";
        }
        else if (!preg_match("/^[0-9+()\-\s]+$/", $contact_no)) {
            echo "Contact Number may only contain numbers, spaces, +, -, and parentheses.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $sss_no)) {
            echo "SSS Number may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $philhealth_no)) {
            echo "PhilHealth Number may only contain numbers, spaces, and hyphens.";
        }
        else if ($pagibig_no != "" && !preg_match("/^[0-9\-\s]+$/", $pagibig_no)) {
            echo "Pag-IBIG Number may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $tin_no)) {
            echo "TIN may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9]+$/", $atm_no)) {
            echo "ATM Number may only contain numbers.";
        }
        else if (!preg_match("/^[0-9A-Za-z-]+$/", $department_no)) {
            echo "Invalid Department Number.";
        }
        else if (!preg_match("/^[0-9A-Za-z-]+$/", $position_no)) {
            echo "Invalid Position Number.";
        }
        else if (!is_numeric($salary) || $salary <= 0) {
            echo "Salary must be a number greater than zero.";
        }
        else if (!in_array($rate_type, ["Hourly", "Daily", "Monthly"], true)) {
            echo "Invalid rate type selected.";
        }
        else if ($birth_date > date("Y-m-d")) {
            echo "Birth Date cannot be in the future.";
        }
        else if ($ending_day != "" && $ending_day < $starting_day) {
            echo "Ending Date cannot be earlier than Starting Date.";
        }
        else {
            $photo = "";
            $photo_valid = true;

            if (isset($_FILES["employee_photo"]) && $_FILES["employee_photo"]["error"] == 0) {
                $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];

                if (!in_array($_FILES["employee_photo"]["type"], $allowed_types, true)) {
                    echo "Photo must be a JPG, PNG, GIF, or WEBP image.";
                    $photo_valid = false;
                }
                else if ($_FILES["employee_photo"]["size"] > 5242880) {
                    echo "Photo must not be larger than 5 MB.";
                    $photo_valid = false;
                }
                else {
                    $photo = mysqli_real_escape_string(
                        $conn,
                        file_get_contents($_FILES["employee_photo"]["tmp_name"])
                    );
                }
            }

            if ($photo_valid) {
                $conn->query("INSERT INTO payroll_personal_info
                (employee_no,password,photo,last_name,first_name,middle_name,gender,birth_date,civil_status,nationality,religion,email,contact_no,permanent_address,present_address,sss_no,philhealth_no,pagibig_no,tin_no,atm_no,department_no,position_no,starting_day,ending_day,salary,rate_type)
                VALUES
                ('$employee_no','$password','$photo','$last_name','$first_name','$middle_name','$gender','$birth_date','$civil_status','$nationality','$religion','$email','$contact_no','$permanent_address','$present_address','$sss_no','$philhealth_no','$pagibig_no','$tin_no','$atm_no','$department_no','$position_no','$starting_day','$ending_day','$salary','$rate_type')");

                header("Location: employee.php?main=employees&section=personal_info_section");
                exit;
            }
        }
    }
    else if (isset($_POST["edit_employee"])) {

        $employee_no = $_POST["employee_no"];

        $sql = "SELECT * FROM payroll_personal_info 
                WHERE employee_no = '$employee_no'";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $employee = $result->fetch_assoc();
            $employee_edit_mode = true;
        }

    }
    else if (isset($_POST["delete_employee"])) {

        $employee_no = $_POST["employee_no"];

        $sql = "DELETE FROM payroll_personal_info 
                WHERE employee_no = '$employee_no'";

        $conn->query($sql);

        header("Location: employee.php?main=list");
        exit();

    }
    else if (isset($_POST["update_employee"])) {

        $old_employee_no = $_POST["old_employee_no"];
        $employee_no = $_POST["employee_no"];
        $password = $_POST["employee_password"];
        $last_name = $_POST["employee_last_name"];
        $first_name = $_POST["employee_first_name"];
        $middle_name = $_POST["employee_middle_name"];
        $gender = $_POST["employee_gender"];
        $birth_date = $_POST["employee_birth_date"];
        $civil_status = $_POST["employee_civil_status"];
        $nationality = $_POST["employee_nationality"];
        $religion = $_POST["employee_religion"];
        $email = $_POST["employee_email"];
        $contact_no = $_POST["employee_contact"];
        $permanent_address = $_POST["employee_permanent_address"];
        $present_address = $_POST["employee_present_address"];
        $sss_no = $_POST["employee_sss"];
        $philhealth_no = $_POST["employee_philhealth"];
        $pagibig_no = $_POST["employee_pagibig"];
        $tin_no = $_POST["employee_tin"];
        $atm_no = $_POST["employee_atm"];
        $department_no = $_POST["employee_department"];
        $position_no = $_POST["employee_position"];
        $starting_day = $_POST["employee_starting_day"];
        $ending_day = $_POST["employee_ending_day"];
        $salary = $_POST["employee_salary"];
        $rate_type = $_POST["employee_rate_type"];

        if (!preg_match("/^[A-Za-z0-9-]+$/", $employee_no)) {
            $employee_error = "Employee Number may only contain letters, numbers, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $last_name)) {
            $employee_error = "Last Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $first_name)) {
            $employee_error = "First Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if ($middle_name != "" && !preg_match("/^[\p{L}\s.'-]+$/u", $middle_name)) {
            $employee_error = "Middle Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!in_array($gender, ["M", "F", "O"], true)) {
            $employee_error = "Invalid gender selected.";
        }
        else if (!in_array($civil_status, ["S", "M", "W", "LS"], true)) {
            $employee_error = "Invalid civil status selected.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $nationality)) {
            $employee_error = "Nationality may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\s.'-]+$/u", $religion)) {
            $employee_error = "Religion may only contain letters, spaces, periods, apostrophes, and hyphens.";
        }
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $employee_error = "Please enter a valid email address.";
        }
        else if (!preg_match("/^[0-9+()\-\s]+$/", $contact_no)) {
            $employee_error = "Contact Number may only contain numbers, spaces, +, -, and parentheses.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $sss_no)) {
            $employee_error = "SSS Number may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $philhealth_no)) {
            $employee_error = "PhilHealth Number may only contain numbers, spaces, and hyphens.";
        }
        else if ($pagibig_no != "" && !preg_match("/^[0-9\-\s]+$/", $pagibig_no)) {
            $employee_error = "Pag-IBIG Number may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9\-\s]+$/", $tin_no)) {
            $employee_error = "TIN may only contain numbers, spaces, and hyphens.";
        }
        else if (!preg_match("/^[0-9]+$/", $atm_no)) {
            $employee_error = "ATM Number may only contain numbers.";
        }
        else if (!preg_match("/^[0-9A-Za-z-]+$/", $department_no)) {
            $employee_error = "Invalid Department Number.";
        }
        else if (!preg_match("/^[0-9A-Za-z-]+$/", $position_no)) {
            $employee_error = "Invalid Position Number.";
        }
        else if (!is_numeric($salary) || $salary <= 0) {
            $employee_error = "Salary must be a number greater than zero.";
        }
        else if (!in_array($rate_type, ["Hourly", "Daily", "Monthly"], true)) {
            $employee_error = "Invalid rate type selected.";
        }
        else if ($birth_date > date("Y-m-d")) {
            $employee_error = "Birth Date cannot be in the future.";
        }
        else if ($ending_day != "" && $ending_day < $starting_day) {
            $employee_error = "Ending Date cannot be earlier than Starting Date.";
        }
        else {
            if ($password == "") {
                $password_result = $conn->query("SELECT password FROM payroll_personal_info WHERE employee_no='$old_employee_no'");
                $password_row = $password_result->fetch_assoc();
                $password = $password_row["password"] ?? "";
            }

            $photo_sql = "";
            $photo_valid = true;

            if (isset($_FILES["employee_photo"]) && $_FILES["employee_photo"]["error"] == 0) {
                $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];

                if (!in_array($_FILES["employee_photo"]["type"], $allowed_types, true)) {
                    $employee_error = "Photo must be a JPG, PNG, GIF, or WEBP image.";
                    $photo_valid = false;
                }
                else if ($_FILES["employee_photo"]["size"] > 5242880) {
                    $employee_error = "Photo must not be larger than 5 MB.";
                    $photo_valid = false;
                }
                else {
                    $photo = mysqli_real_escape_string(
                        $conn,
                        file_get_contents($_FILES["employee_photo"]["tmp_name"])
                    );
                    $photo_sql = ", photo='$photo'";
                }
            }

            if ($photo_valid) {
                $update_result = $conn->query("UPDATE payroll_personal_info SET
                    employee_no='$employee_no', password='$password', last_name='$last_name',
                    first_name='$first_name', middle_name='$middle_name', gender='$gender',
                    birth_date='$birth_date', civil_status='$civil_status', nationality='$nationality',
                    religion='$religion', email='$email', contact_no='$contact_no',
                    permanent_address='$permanent_address', present_address='$present_address',
                    sss_no='$sss_no', philhealth_no='$philhealth_no', pagibig_no='$pagibig_no',
                    tin_no='$tin_no', atm_no='$atm_no', department_no='$department_no',
                    position_no='$position_no', starting_day='$starting_day', ending_day='$ending_day',
                    salary='$salary', rate_type='$rate_type' $photo_sql
                    WHERE employee_no='$old_employee_no'");

                if (!$update_result) {
                    $employee_error = "Update failed: " . $conn->error;
                }
                else {
                    header("Location: employee.php?main=list");
                    exit;
                }
            }
        }

        if ($employee_error != "") {
            $employee_edit_mode = true;
            $employee = [
                "employee_no" => $employee_no, "last_name" => $last_name,
                "first_name" => $first_name, "middle_name" => $middle_name,
                "gender" => $gender, "birth_date" => $birth_date,
                "civil_status" => $civil_status, "nationality" => $nationality,
                "religion" => $religion, "email" => $email,
                "contact_no" => $contact_no, "permanent_address" => $permanent_address,
                "present_address" => $present_address, "sss_no" => $sss_no,
                "philhealth_no" => $philhealth_no, "pagibig_no" => $pagibig_no,
                "tin_no" => $tin_no, "atm_no" => $atm_no,
                "department_no" => $department_no, "position_no" => $position_no,
                "starting_day" => $starting_day, "ending_day" => $ending_day,
                "salary" => $salary, "rate_type" => $rate_type
            ];
        }
    }

    if (isset($_POST["save_emergency"])) {
        $employee_no = $_POST["emergency_employee_no"];

        if ($employee_no == "") {
            echo "Please select an employee.";
        }
        else if (!isset($_POST["emergency_name"]) || count($_POST["emergency_name"]) == 0) {
            echo "Please add an emergency contact.";
        }
        else {
            $valid = true;

            for ($i = 0; $i < count($_POST["emergency_name"]); $i++) {
                $name = $_POST["emergency_name"][$i];
                $relationship = $_POST["emergency_relationship"][$i];
                $contact = $_POST["emergency_contact"][$i];

                if (!preg_match("/^[\p{L}\s.'-]+$/u", $name)) {
                    echo "Emergency Contact Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[\p{L}\s.'-]+$/u", $relationship)) {
                    echo "Relationship may only contain letters, spaces, periods, apostrophes, and hyphens.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[0-9+()\-\s]+$/", $contact)) {
                    echo "Contact Number may only contain numbers, spaces, +, -, and parentheses.";
                    $valid = false;
                    break;
                }
            }

            // SAVE ALL INPUTS
            if ($valid) {
                for ($i = 0; $i < count($_POST["emergency_name"]); $i++) {
                    $name = $_POST["emergency_name"][$i];
                    $relationship = $_POST["emergency_relationship"][$i];
                    $address = $_POST["emergency_address"][$i];
                    $contact = $_POST["emergency_contact"][$i];

                    $conn->query("INSERT INTO payroll_emergency_contacts
                    (employee_no,name,relationship,address,contact_no)
                    VALUES
                    ('$employee_no','$name','$relationship','$address','$contact')");
                }

                header("Location: employee.php?main=employees&section=emergency_contact_section");
                exit;
            }
        }
    }

    if (isset($_POST["save_dependent"])) {
        $employee_no = $_POST["dependent_employee_no"];

        if ($employee_no == "") {
            echo "Please select an employee.";
        }
        else if (!isset($_POST["dependent_name"]) || count($_POST["dependent_name"]) == 0) {
            echo "Please add a dependent.";
        }
        else {
            $valid = true;

            for ($i = 0; $i < count($_POST["dependent_name"]); $i++) {
                $name = $_POST["dependent_name"][$i];
                $relationship = $_POST["dependent_relationship"][$i];

                if (!preg_match("/^[\p{L}\s.'-]+$/u", $name)) {
                    echo "Dependent Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
                    $valid = false;
                    break;
                }
                else if ($birth_date > date("Y-m-d")) {
                    echo "Dependent Birth Date cannot be in the future.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[\p{L}\s.'-]+$/u", $relationship)) {
                    echo "Relationship may only contain letters, spaces, periods, apostrophes, and hyphens.";
                    $valid = false;
                    break;
                }
            }

            // SAVE ALL INPUTS
            if ($valid) {
                for ($i = 0; $i < count($_POST["dependent_name"]); $i++) {
                    $name = $_POST["dependent_name"][$i];
                    $birth_date = $_POST["dependent_birth_date"][$i];
                    $relationship = $_POST["dependent_relationship"][$i];

                    $conn->query("INSERT INTO payroll_dependents
                    (employee_no,name,birth_date,relationship)
                    VALUES
                    ('$employee_no','$name','$birth_date','$relationship')");
                }

                header("Location: employee.php?main=employees&section=dependent_section");
                exit;
            }
        }
    }

    if (isset($_POST["save_education"])) {
        $employee_no = $_POST["education_employee_no"];

        if ($employee_no == "") {
            echo "Please select an employee.";
        }
        else if (!isset($_POST["school"]) || count($_POST["school"]) == 0) {
            echo "Please add an educational background.";
        }
        else {
            $valid = true;

            for ($i = 0; $i < count($_POST["school"]); $i++) {
                $school = $_POST["school"][$i];
                $school_year = $_POST["school_year"][$i];
                $level = $_POST["education_level"][$i];

                if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $school)) {
                    echo "School may only contain letters, numbers, spaces, and common punctuation.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[0-9\-]+$/", $school_year)) {
                    echo "School Year may only contain numbers and hyphens.";
                    $valid = false;
                    break;
                }
                else if (!in_array($level, ["Elementary", "Junior High School", "Senior High School", "College", "Vocational", "Graduate School"], true)) {
                    echo "Invalid Education Level selected.";
                    $valid = false;
                    break;
                }
            }

            // SAVE ALL INPUTS
            if ($valid) {
                for ($i = 0; $i < count($_POST["school"]); $i++) {
                    $school = $_POST["school"][$i];
                    $school_address = $_POST["school_address"][$i];
                    $school_year = $_POST["school_year"][$i];
                    $level = $_POST["education_level"][$i];

                    $conn->query("INSERT INTO payroll_educational_background
                    (employee_no,school,school_address,school_year,level)
                    VALUES
                    ('$employee_no','$school','$school_address','$school_year','$level')");
                }

                header("Location: employee.php?main=employees&section=educational_background_section");
                exit;
            }
        }
    }

    if (isset($_POST["save_reference"])) {
        $employee_no = $_POST["reference_employee_no"];

        if ($employee_no == "") {
            echo "Please select an employee.";
        }
        else if (!isset($_POST["reference_name"]) || count($_POST["reference_name"]) == 0) {
            echo "Please add a character reference.";
        }
        else {
            $valid = true;

            for ($i = 0; $i < count($_POST["reference_name"]); $i++) {
                $name = $_POST["reference_name"][$i];
                $occupation = $_POST["reference_occupation"][$i];
                $contact = $_POST["reference_contact"][$i];

                if (!preg_match("/^[\p{L}\s.'-]+$/u", $name)) {
                    echo "Reference Name may only contain letters, spaces, periods, apostrophes, and hyphens.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $occupation)) {
                    echo "Occupation may only contain letters, numbers, spaces, and common punctuation.";
                    $valid = false;
                    break;
                }
                else if (!preg_match("/^[0-9+()\-\s]+$/", $contact)) {
                    echo "Contact Number may only contain numbers, spaces, +, -, and parentheses.";
                    $valid = false;
                    break;
                }
            }

            // SAVE ALL INPUTS
            if ($valid) {
                for ($i = 0; $i < count($_POST["reference_name"]); $i++) {
                    $name = $_POST["reference_name"][$i];
                    $occupation = $_POST["reference_occupation"][$i];
                    $address = $_POST["reference_address"][$i];
                    $contact = $_POST["reference_contact"][$i];

                    $conn->query("INSERT INTO payroll_character_references
                    (employee_no,name,occupation,address,contact_no)
                    VALUES
                    ('$employee_no','$name','$occupation','$address','$contact')");
                }

                header("Location: employee.php?main=employees&section=character_reference_section");
                exit;
            }
        }
    }

    $departments = $conn->query("SELECT department_no, department_name FROM payroll_departments ORDER BY department_name");

    $positions = $conn->query("SELECT position_no, position_name FROM payroll_positions ORDER BY position_name");

    if (isset($_POST["new_department"])) {

        $department_no = $_POST["new_department_no"];
        $department_name = $_POST["new_department_name"];
        $department_color = $_POST["department_color"];

        $department_logo = null;

        if (isset($_FILES["department_logo"]) &&
            $_FILES["department_logo"]["error"] == 0) {

            if ($_FILES["department_logo"]["size"] > 5242880) {
                echo "Department logo must not be larger than 5 MB.";
            }
            else if (getimagesize($_FILES["department_logo"]["tmp_name"]) === false) {
                echo "Department logo must be an image.";
            }
            else {
                $department_logo = mysqli_real_escape_string(
                    $conn,
                    file_get_contents($_FILES["department_logo"]["tmp_name"])
                );
            }
        }

        if (!preg_match("/^[0-9A-Za-z-]+$/", $department_no)) {

            echo "Department Number may only contain letters, numbers, and hyphens.";

        }
        else if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $department_name)) {

            echo "Department Name contains invalid characters.";

        }
        else {

            $logo_sql = $department_logo !== null
                ? "'$department_logo'"
                : "NULL";

            $conn->query("INSERT INTO payroll_departments
                (department_no, department_name, department_logo, department_color)
                VALUES
                ('$department_no', '$department_name', $logo_sql, '$department_color')");

            header("Location: employee.php?main=categories&section=department_section");
            exit;
        }
    }
    else if (isset($_POST["edit_department"])) {

        $department_no = $_POST["department_no"];

        $result = $conn->query("SELECT department_no, department_name,
        department_logo, department_color
        FROM payroll_departments
        WHERE department_no = '$department_no'");

        if ($result && $result->num_rows > 0) {
            $department = $result->fetch_assoc();
            $department_edit_mode = true;
        }

    }
    else if (isset($_POST["delete_department"])) {

        $department_no = $_POST["department_no"];

        $conn->query("DELETE FROM payroll_departments
            WHERE department_no = '$department_no'");

        header("Location: employee.php?main=categories&section=department_section");
        exit;

    }
    else if (isset($_POST["update_department"])) {

        $old_department_no = $_POST["old_department_no"];
        $new_department_no = $_POST["new_department_no"];
        $new_department_name = $_POST["new_department_name"];
        $department_color = $_POST["department_color"];

        if (!preg_match("/^[0-9A-Za-z-]+$/", $new_department_no)) {

            echo "Department Number may only contain letters, numbers, and hyphens.";

        }
        else if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $new_department_name)) {

            echo "Department Name contains invalid characters.";

        }
        else {

            if (isset($_FILES["department_logo"]) &&
                $_FILES["department_logo"]["error"] == 0) {

                if ($_FILES["department_logo"]["size"] > 5242880) {

                    echo "Department logo must not be larger than 5 MB.";

                }
                else if (getimagesize($_FILES["department_logo"]["tmp_name"]) === false) {

                    echo "Department logo must be an image.";

                }
                else {

                    $department_logo = mysqli_real_escape_string(
                        $conn,
                        file_get_contents($_FILES["department_logo"]["tmp_name"])
                    );

                    $conn->query("UPDATE payroll_departments SET
                        department_no='$new_department_no',
                        department_name='$new_department_name',
                        department_logo='$department_logo',
                        department_color='$department_color'
                        WHERE department_no='$old_department_no'");

                    header("Location: employee.php?main=categories&section=department_section");
                    exit;
                }

            }
            else {

                $conn->query("UPDATE payroll_departments SET
                    department_no='$new_department_no',
                    department_name='$new_department_name',
                    department_color='$department_color'
                    WHERE department_no='$old_department_no'");

                header("Location: employee.php?main=categories&section=department_section");
                exit;
            }
        }
    }

    if (isset($_POST["new_position"])) {

        $position_no = $_POST["new_position_no"];
        $position_name = $_POST["new_position_name"];

        if (!preg_match("/^[0-9A-Za-z-]+$/", $position_no)) {
            echo "Position Number may only contain letters, numbers, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $position_name)) {
            echo "Position Name contains invalid characters.";
        }
        else {
            $conn->query("INSERT INTO payroll_positions
            (position_no, position_name)
            VALUES
            ('$position_no', '$position_name')");

            header("Location: employee.php?main=categories&section=position_section");
            exit;
        }

    }
    else if (isset($_POST["edit_position"])) {

        $position_no = $_POST["position_no"];

        $result = $conn->query("SELECT position_no, position_name
            FROM payroll_positions
            WHERE position_no = '$position_no'");

        if ($result && $result->num_rows > 0) {
            $position = $result->fetch_assoc();
            $position_edit_mode = true;
        }

    }
    else if (isset($_POST["delete_position"])) {

        $position_no = $_POST["position_no"];

        $conn->query("DELETE FROM payroll_positions
            WHERE position_no = '$position_no'");

        header("Location: employee.php?main=categories&section=position_section");
        exit;

    }
    else if (isset($_POST["update_position"])) {

        $old_position_no = $_POST["old_position_no"];
        $new_position_no = $_POST["new_position_no"];
        $new_position_name = $_POST["new_position_name"];

        if (!preg_match("/^[0-9A-Za-z-]+$/", $new_position_no)) {
            echo "Position Number may only contain letters, numbers, and hyphens.";
        }
        else if (!preg_match("/^[\p{L}\p{N}\s&.,'()#\/+-]+$/u", $new_position_name)) {
            echo "Position Name contains invalid characters.";
        }
        else {
            $conn->query("UPDATE payroll_positions SET
                position_no='$new_position_no',
                position_name='$new_position_name'
                WHERE position_no='$old_position_no'");

            header("Location: employee.php?main=categories&section=position_section");
            exit;
        }

    }

    if (isset($_GET["manage_employee"])) {

        $employee_no = $_GET["manage_employee"];

        $stmt = $conn->prepare("
            SELECT p.*,
                   d.department_name,
                   pos.position_name
            FROM payroll_personal_info p
            LEFT JOIN payroll_departments d
                ON p.department_no = d.department_no
            LEFT JOIN payroll_positions pos
                ON p.position_no = pos.position_no
            WHERE p.employee_no = ?
        ");

        $stmt->bind_param("s", $employee_no);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $employee = $result->fetch_assoc();

            // Do not send the password to the browser
            unset($employee["password"]);

            // Convert photo to Base64 if one exists
            if (!empty($employee["photo"])) {
                $employee["photo"] =
                    "data:image/jpeg;base64," .
                    base64_encode($employee["photo"]);
            } else {
                $employee["photo"] = "";
            }

            header("Content-Type: application/json");

            echo json_encode([
                "success" => true,
                "employee" => $employee
            ]);

        } else {

            header("Content-Type: application/json");

            echo json_encode([
                "success" => false
            ]);
        }

        exit;
    }

    // DASHBOARD DATA

    // Total Employees
    $dashboard_total_employees = $conn->query(
        "SELECT COUNT(*) AS total
        FROM payroll_personal_info"
    )->fetch_assoc()["total"];


    // Active Employees
    $dashboard_active_employees = $conn->query(
        "SELECT COUNT(*) AS total
        FROM payroll_personal_info
        WHERE ending_day IS NULL OR ending_day >= CURDATE()"
    )->fetch_assoc()["total"];


    // Inactive Employees
    $dashboard_inactive_employees = $conn->query(
        "SELECT COUNT(*) AS total
        FROM payroll_personal_info
        WHERE ending_day IS NOT NULL AND ending_day < CURDATE()"
    )->fetch_assoc()["total"];


    // Total Departments
    $dashboard_departments = $conn->query(
        "SELECT COUNT(*) AS total
        FROM payroll_departments"
    )->fetch_assoc()["total"];


    // Total Positions
    $dashboard_positions = $conn->query(
        "SELECT COUNT(*) AS total
        FROM payroll_positions"
    )->fetch_assoc()["total"];


    // Employees by Department
    $dashboard_department_list = $conn->query(
        "SELECT
            d.department_no,
            d.department_name,
            d.department_logo,
            d.department_color,
            COALESCE(e.employee_count, 0) AS employee_count
        FROM payroll_departments d
        LEFT JOIN (
            SELECT
                department_no,
                COUNT(DISTINCT employee_no) AS employee_count
            FROM payroll_personal_info
            GROUP BY department_no
        ) e
            ON d.department_no = e.department_no
        ORDER BY d.department_name"
    );


    // Employee salary information for each department and position
    $dashboard_payroll_result = $conn->query(
        "SELECT
            pi.department_no,
            pi.position_no,
            p.position_name,
            pi.salary,
            pi.rate_type
        FROM payroll_personal_info pi
        LEFT JOIN payroll_positions p
            ON pi.position_no = p.position_no
        WHERE pi.department_no IS NOT NULL
        ORDER BY pi.department_no, p.position_name, pi.salary, pi.rate_type"
    );

    $dashboard_payroll = [];

    while ($payroll = $dashboard_payroll_result->fetch_assoc()) {

        $department_no = $payroll["department_no"];
        $position_name = !empty($payroll["position_name"])
            ? $payroll["position_name"]
            : "No Position";

        $salary = $payroll["salary"];
        $rate_type = !empty($payroll["rate_type"])
            ? $payroll["rate_type"]
            : "No Rate Type";

        if (!isset($dashboard_payroll[$department_no])) {
            $dashboard_payroll[$department_no] = [];
        }

        if (!isset($dashboard_payroll[$department_no][$position_name])) {
            $dashboard_payroll[$department_no][$position_name] = [];
        }

        $rate_key = $salary . "|" . $rate_type;

        if (!isset($dashboard_payroll[$department_no][$position_name][$rate_key])) {
            $dashboard_payroll[$department_no][$position_name][$rate_key] = [
                "salary" => $salary,
                "rate_type" => $rate_type,
                "count" => 0
            ];
        }

        $dashboard_payroll[$department_no][$position_name][$rate_key]["count"]++;
    }
?>


<html>
    <head>

        <title>Payroll</title>

        <link rel="stylesheet" href="employee_style.css">

    </head>

    <body>

        <header class="site-header">

            <div class="school-top-bar"></div>

            <div class="school-branding">

                <img src="lcc_logo.png" class="school-logo" alt="Lipa City Colleges Logo">

                <div class="school-name">

                    <div>LIPA CITY COLLEGES</div>

                    <span>PAYROLL & EMPLOYEE MANAGEMENT SYSTEM</span>

                </div>

                <img src="lcc_silvercrest_logo.png" class="silvercrest-logo" alt="LCC Silvercrest Logo">

            </div>

            <nav class="app-nav">
                <div class="app-nav-links">

                    <a href="#" onclick="show_main('dashboard')">Dashboard</a>

                    <a href="#" onclick="show_main('list')">Employees</a>

                    <a href="#" onclick="show_main('reports')">Reports</a>

                    <div class="nav-dropdown" id="employee_management">
                        <button type="button">
                            Manage
                        </button>

                        <div id="employee_menu">
                            <a href="#" onclick="show_main('employees')">
                                Employee
                            </a>

                            <a href="#" onclick="show_main('categories')">
                                Category
                            </a>
                        </div>
                    </div>

                    <a href="employee_logout.php">Logout</a>

                </div>
            </nav>

        </header>

        <div id="dashboard" class="main_nav">

            <h1>DASHBOARD</h1>


            <!-- EMPLOYEE SUMMARY -->

            <div class="dashboard_summary">

                <div class="dashboard_card dashboard_total_card">

                    <h2>TOTAL EMPLOYEES</h2>

                    <p>
                        <?= $dashboard_total_employees ?>
                    </p>

                </div>


                <div class="dashboard_card">

                    <h2>ACTIVE EMPLOYEES</h2>

                    <p>
                        <?= $dashboard_active_employees ?>
                    </p>

                </div>


                <div class="dashboard_card">

                    <h2>INACTIVE EMPLOYEES</h2>

                    <p>
                        <?= $dashboard_inactive_employees ?>
                    </p>

                </div>


                <div class="dashboard_card">

                    <h2>DEPARTMENTS</h2>

                    <p>
                        <?= $dashboard_departments ?>
                    </p>

                </div>


                <div class="dashboard_card">

                    <h2>POSITIONS</h2>

                    <p>
                        <?= $dashboard_positions ?>
                    </p>

                </div>

            </div>


            <!-- DEPARTMENTS -->

            <div class="dashboard_departments">

                <h2>DEPARTMENTS</h2>


                <div class="department_cards">

                    <?php while ($dashboard_department = $dashboard_department_list->fetch_assoc()): ?>

                        <?php

                            $department_color =
                                !empty($dashboard_department["department_color"])
                                ? $dashboard_department["department_color"]
                                : "#0b145c";

                        ?>

                        <div
                            class="department_card"
                            style="--department-color: <?= htmlspecialchars($department_color) ?>;"
                        >


                            <?php if (!empty($dashboard_department["department_logo"])): ?>

                                <div class="department_card_logo">

                                    <img
                                        src="data:image/jpeg;base64,<?= base64_encode($dashboard_department["department_logo"]) ?>"
                                        alt=""
                                    >

                                </div>

                            <?php endif; ?>


                            <div class="department_card_content">

                                <h3>
                                    <?= htmlspecialchars($dashboard_department["department_name"]) ?>
                                </h3>


                                <div class="department_employee_count">

                                    <strong>
                                        <?= $dashboard_department["employee_count"] ?>
                                    </strong>

                                    <span>
                                        Employees
                                    </span>

                                </div>

                                <div class="department_card_info">

                                    <?php
                                        $department_no = $dashboard_department["department_no"];

                                        if (isset($dashboard_payroll[$department_no])):

                                            foreach ($dashboard_payroll[$department_no] as $position_name => $rates):
                                    ?>

                                        <div class="department_position">

                                            <strong>
                                                <?= htmlspecialchars($position_name) ?>
                                            </strong>

                                            <span>
                                                <?php
                                                    $position_count = 0;

                                                    foreach ($rates as $rate) {
                                                        $position_count += $rate["count"];
                                                    }

                                                    echo $position_count . " employee";
                                                    echo $position_count != 1 ? "s" : "";
                                                ?>
                                            </span>

                                            <div class="department_position_rates">

                                                <?php foreach ($rates as $rate): ?>

                                                    <div>
                                                        ₱<?= number_format((float)$rate["salary"], 2) ?>
                                                        / <?= htmlspecialchars($rate["rate_type"]) ?>

                                                        <?php if ($rate["count"] > 1): ?>
                                                            × <?= $rate["count"] ?>
                                                        <?php endif; ?>
                                                    </div>

                                                <?php endforeach; ?>

                                            </div>

                                        </div>

                                    <?php
                                            endforeach;

                                        else:
                                    ?>

                                        <div>
                                            No employees assigned
                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </div>

        </div>

        <div id="list" class="main_nav">
            <h1>EMPLOYEES</h1>

            <?php
                $employee_list = $conn->query(
                    "SELECT employee_no, last_name, first_name, middle_name, department_no, position_no
                    FROM payroll_personal_info
                    ORDER BY employee_no"
                );

                // Get departments dynamically
                $department_list = $conn->query(
                    "SELECT department_no, department_name
                    FROM payroll_departments
                    ORDER BY department_name"
                );

                $department_names = [];

                while ($department_row = $department_list->fetch_assoc()) {
                    $department_names[$department_row["department_no"]] = $department_row["department_name"];
                }

                // Get positions dynamically
                $position_list = $conn->query(
                    "SELECT position_no, position_name
                    FROM payroll_positions
                    ORDER BY position_name"
                );

                $position_names = [];

                while ($position_row = $position_list->fetch_assoc()) {
                    $position_names[$position_row["position_no"]] = $position_row["position_name"];
                }
            ?>

            <!-- FILTERS -->
            <div class="employee_filters">

                <input
                    type="text"
                    id="employee_search"
                    placeholder="Search by ID or name..."
                    oninput="filter_employees()"
                >

                <select id="department_filter" onchange="filter_employees()">
                    <option value="">All Departments</option>

                    <?php foreach ($department_names as $department_no => $department_name): ?>
                        <option value="<?= htmlspecialchars($department_no) ?>">
                            <?= htmlspecialchars($department_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="position_filter" onchange="filter_employees()">
                    <option value="">All Positions</option>

                    <?php foreach ($position_names as $position_no => $position_name): ?>
                        <option value="<?= htmlspecialchars($position_no) ?>">
                            <?= htmlspecialchars($position_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>


            <!-- EMPLOYEE TABLE -->
            <table id="employee_table">
                <tr>
                    <th rowspan="2">No.</th>
                    <th colspan="3">Name</th>
                    <th rowspan="2">Department</th>
                    <th rowspan="2">Position</th>
                    <th rowspan="2">Actions</th>
                </tr>
                <tr>
                    <th>Last</th>
                    <th>First</th>
                    <th>Middle</th>
                </tr>
                <?php while ($employee_row = $employee_list->fetch_assoc()): ?>
                    <tr
                        class="employee_row"
                        data-employee-no="<?= htmlspecialchars($employee_row['employee_no']) ?>"
                        data-last-name="<?= htmlspecialchars($employee_row['last_name']) ?>"
                        data-first-name="<?= htmlspecialchars($employee_row['first_name']) ?>"
                        data-middle-name="<?= htmlspecialchars($employee_row['middle_name']) ?>"
                        data-department="<?= htmlspecialchars($employee_row['department_no']) ?>"
                        data-position="<?= htmlspecialchars($employee_row['position_no']) ?>"
                        data-department-name="<?= htmlspecialchars($department_names[$employee_row['department_no']] ?? 'N/A') ?>"
                        data-position-name="<?= htmlspecialchars($position_names[$employee_row['position_no']] ?? 'N/A') ?>"
                    >

                        <td><?= htmlspecialchars($employee_row['employee_no']) ?></td>
                        <td><?= htmlspecialchars($employee_row['last_name']) ?></td>
                        <td><?= htmlspecialchars($employee_row['first_name']) ?></td>
                        <td><?= htmlspecialchars($employee_row['middle_name']) ?></td>
                        <td>
                            <?= htmlspecialchars($department_names[$employee_row['department_no']] ?? 'N/A') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($position_names[$employee_row['position_no']] ?? 'N/A') ?>
                        </td>
                        <td>
                            <button
                                type="button"
                                onclick="manage_employee('<?= htmlspecialchars($employee_row['employee_no'], ENT_QUOTES) ?>')"
                            >
                                View
                            </button>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

            
            
            <!-- MANAGE EMPLOYEE POPUP -->

            <div id="employee_manage_popup" class="employee_popup">

                <div class="employee_popup_box">

                    <div class="employee_popup_header">

                        <h2>Employee Information</h2>

                        <button
                            type="button"
                            class="employee_popup_close"
                            onclick="close_employee_popup()"
                        >
                            &times;
                        </button>

                    </div>


                    <div
                        id="employee_popup_information"
                        class="employee_popup_information"
                    >
                    </div>


                    <div class="employee_popup_actions">

                        <form action="employee.php" method="post" style="display:inline;">
                            <input type="hidden" name="employee_no" id="popup_employee_no">
                            <button type="submit" name="edit_employee" class="employee_edit_button">Edit</button>
                        </form>

                        <form action="employee.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                            <input type="hidden" name="employee_no" id="delete_employee_no">
                            <button type="submit" name="delete_employee" class="employee_delete_button">Delete</button>
                        </form>

                    </div>

                </div>

            </div>

        </div>

        <div id="employees" class="main_nav">

            <h1>EMPLOYEE</h1>

            <nav>
                <a href="#" onclick="show_section('employees','personal_info_section')">Personal Information</a>
                <a href="#" onclick="show_section('employees','emergency_contact_section')">Emergency Contact</a>
                <a href="#" onclick="show_section('employees','dependent_section')">Dependent</a>
                <a href="#" onclick="show_section('employees','educational_background_section')">Educational Background</a>
                <a href="#" onclick="show_section('employees','character_reference_section')">Character Reference</a>
            </nav>


            <section id="personal_info_section">

                <form action="employee.php" method="post"
                    enctype="multipart/form-data"
                    onsubmit="return confirm('<?php echo $department_edit_mode ? "Are you sure you want to update this department?" : "Are you sure you want to add this department?"; ?>');">
                    <?php if ($employee_error != "") { ?>
                        <p style="color:red; font-weight:bold;">Error: <?php echo htmlspecialchars($employee_error); ?></p>
                    <?php } ?>
                    <h3>Account Information</h3>

                    <label for="employee_photo">Photo</label>
                    
                    <input type="file" id="employee_photo" name="employee_photo"
                        accept="image/*" onchange="preview_image()">

                    <br>

                    <img id="photo_preview"
                        src="<?php echo $employee_edit_mode && !empty($employee['photo']) ? 'data:image/jpeg;base64,' . base64_encode($employee['photo']) : ''; ?>"
                        width="150"
                        style="<?php echo $employee_edit_mode && !empty($employee['photo']) ? '' : 'display:none;'; ?>">

                    <br><br>

                    <label for="employee_no">Employee Number</label>
                    <input type="text" name="employee_no" id="employee_no" required
                        value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['employee_no'] ?? '') : ''; ?>" maxlength="30">

                    <?php if ($employee_edit_mode) { ?>             
                        <input type="hidden" name="old_employee_no" value="<?php echo htmlspecialchars($employee['employee_no'] ?? ''); ?>" >
                    <?php } ?>

                    <label for="employee_password">Password</label>
                    <input type="password" id="employee_password"
                        name="employee_password" <?php echo $employee_edit_mode ? "" : "required"; ?>>

                    <h3>Basic Information</h3>

                    <label for="employee_last_name">Last Name</label>
                    <input type="text" name="employee_last_name" id="employee_last_name" required
                        value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['last_name'] ?? '') : ''; ?>" maxlength="100">

                    <label for="employee_first_name">First Name</label>
                    <input type="text" name="employee_first_name" id="employee_first_name" required
                        value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['first_name'] ?? '') : ''; ?>" maxlength="100">

                    <label for="employee_middle_name">Middle Name</label>
                    <input type="text" name="employee_middle_name" id="employee_middle_name"
                        value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['middle_name'] ?? '') : ''; ?>" maxlength="100">

                    <br><br>

                    <label for="employee_gender">Gender</label>
                    <select id="employee_gender" name="employee_gender">
                        <option value="M" <?php echo $employee_edit_mode && ($employee['gender'] ?? '') == 'M' ? 'selected' : ''; ?>>Male</option>
                        <option value="F" <?php echo $employee_edit_mode && ($employee['gender'] ?? '') == 'F' ? 'selected' : ''; ?>>Female</option>
                        <option value="O" <?php echo $employee_edit_mode && ($employee['gender'] ?? '') == 'O' ? 'selected' : ''; ?>>Other</option>
                    </select>

                    <label for="employee_birth_date">Birth Date</label>
                    <input type="date" id="employee_birth_date"
                        name="employee_birth_date" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['birth_date'] ?? '') : ''; ?>">

                    <br><br>

                    <label for="employee_civil_status">Civil Status</label>
                    <select id="employee_civil_status" name="employee_civil_status">
                        <option value="S" <?php echo $employee_edit_mode && ($employee['civil_status'] ?? '') == 'S' ? 'selected' : ''; ?>>Single</option>
                        <option value="M" <?php echo $employee_edit_mode && ($employee['civil_status'] ?? '') == 'M' ? 'selected' : ''; ?>>Married</option>
                        <option value="W" <?php echo $employee_edit_mode && ($employee['civil_status'] ?? '') == 'W' ? 'selected' : ''; ?>>Widowed</option>
                        <option value="LS" <?php echo $employee_edit_mode && ($employee['civil_status'] ?? '') == 'LS' ? 'selected' : ''; ?>>Legally Separated</option>
                    </select>

                    <label for="employee_nationality">Nationality</label>
                    <input type="text" id="employee_nationality"
                        name="employee_nationality" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['nationality'] ?? '') : ''; ?>" maxlength="100">

                    <label for="employee_religion">Religion</label>
                    <input type="text" id="employee_religion"
                        name="employee_religion" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['religion'] ?? '') : ''; ?>" maxlength="100">

                    <h3>Contact Information</h3>

                    <label for="employee_email">Email Address</label>
                    <input type="text" id="employee_email" name="employee_email" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['email'] ?? '') : ''; ?>" maxlength="150">

                    <label for="employee_contact">Contact No.</label>
                    <input type="text" id="employee_contact" name="employee_contact" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['contact_no'] ?? '') : ''; ?>" maxlength="30">

                    <br><br>

                    <label for="employee_permanent_address">Permanent Address</label>
                    <input type="text" id="employee_permanent_address"
                        name="employee_permanent_address" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['permanent_address'] ?? '') : ''; ?>">

                    <label for="employee_present_address">Present Address</label>
                    <input type="text" id="employee_present_address"
                        name="employee_present_address" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['present_address'] ?? '') : ''; ?>">

                    <h3>Government / Financial Information</h3>

                    <label for="employee_sss">SSS No.</label>
                    <input type="text" id="employee_sss" name="employee_sss" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['sss_no'] ?? '') : ''; ?>" maxlength="30">

                    <label for="employee_philhealth">PhilHealth No.</label>
                    <input type="text" id="employee_philhealth"
                        name="employee_philhealth" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['philhealth_no'] ?? '') : ''; ?>" maxlength="30">

                    <br><br>

                    <label for="employee_pagibig">Pag-IBIG No.</label>
                    <input type="text" id="employee_pagibig"
                        name="employee_pagibig" value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['pagibig_no'] ?? '') : ''; ?>" maxlength="30">

                    <label for="employee_tin">TIN</label>
                    <input type="text" id="employee_tin" name="employee_tin" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['tin_no'] ?? '') : ''; ?>" maxlength="30">

                    <br><br>

                    <label for="employee_atm">ATM No.</label>
                    <input type="text" id="employee_atm" name="employee_atm" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['atm_no'] ?? '') : ''; ?>" maxlength="30">

                    <br><br>

                    <h3>Employment Information</h3>

                    <label for="employee_department">Department</label>
                    <select id="employee_department" name="employee_department" required>
                        <option value="">Select Department</option>

                        <?php while ($row = $departments->fetch_assoc()) { ?>

                            <option value="<?= $row["department_no"] ?>" <?php echo $employee_edit_mode && ($employee['department_no'] ?? '') == $row["department_no"] ? 'selected' : ''; ?>>

                                <?= htmlspecialchars($row["department_name"]) ?>

                            </option>

                        <?php } ?>

                    </select>

                    <label for="employee_position">Position</label>
                    <select id="employee_position" name="employee_position" required>
                        <option value="">Select Position</option>

                        <?php while ($row = $positions->fetch_assoc()) { ?>

                            <option value="<?= $row["position_no"] ?>" <?php echo $employee_edit_mode && ($employee['position_no'] ?? '') == $row["position_no"] ? 'selected' : ''; ?>>

                                <?= htmlspecialchars($row["position_name"]) ?>

                            </option>

                        <?php } ?>

                    </select>

                    <label for="employee_starting_day">Start</label>
                    <input type="date" id="employee_starting_day"
                        name="employee_starting_day" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['starting_day'] ?? '') : ''; ?>">

                    <label for="employee_ending_day">End</label>
                    <input type="date" id="employee_ending_day"
                        name="employee_ending_day" value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['ending_day'] ?? '') : ''; ?>">

                    <label for="employee_salary">Salary</label>
                    <input type="text" id="employee_salary"
                        name="employee_salary" required value="<?php echo $employee_edit_mode ? htmlspecialchars($employee['salary'] ?? '') : ''; ?>" maxlength="15">

                    <label for="employee_rate_type">Rate</label>
                    <select id="employee_rate_type" name="employee_rate_type" required>
                        <option value="Hourly" <?php echo $employee_edit_mode && ($employee['rate_type'] ?? '') == 'Hourly' ? 'selected' : ''; ?>>Hourly</option>
                        <option value="Daily" <?php echo $employee_edit_mode && ($employee['rate_type'] ?? '') == 'Daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="Monthly" <?php echo $employee_edit_mode && ($employee['rate_type'] ?? '') == 'Monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>

                    <br><br>

                    <?php if ($employee_edit_mode) { ?>

                        <button type="submit" name="update_employee">
                            Update Employee
                        </button>

                    <?php } else { ?>

                        <button type="submit" name="save_employee">
                            Save Employee
                        </button>

                    <?php } ?>

                </form>

            </section>


            <section id="emergency_contact_section">

                <h3>Emergency Contacts</h3>

                <form action="employee.php" method="post"
                    onsubmit="return check_employee('emergency_employee_no') && confirm('Save emergency contacts?');">

                    <label>Search Employee</label>

                    <input type="text" id="emergency_employee_search"
                        oninput="search_employee(
                            'emergency_employee_search',
                            'emergency_employee_results',
                            'emergency_employee_no',
                            'selected_emergency_employee'
                        )">

                    <div id="emergency_employee_results"></div>

                    <input type="hidden" name="emergency_employee_no"
                        id="emergency_employee_no">

                    <p id="selected_emergency_employee">

                        No employee selected.

                    </p>

                    <div id="emergency_contact_list"></div>

                    <button type="button" onclick="add_emergency_contact()">+</button>

                    <br><br>

                    <button type="submit" name="save_emergency">

                        Save Emergency Contacts

                    </button>

                </form>

            </section>


            <section id="dependent_section">

                <h3>Dependents</h3>

                <form action="employee.php" method="post"
                    onsubmit="return check_employee('dependent_employee_no') && confirm('Save dependents?');">

                    <label>Search Employee</label>

                    <input type="text" id="dependent_employee_search"
                        oninput="search_employee(
                            'dependent_employee_search',
                            'dependent_employee_results',
                            'dependent_employee_no',
                            'selected_dependent_employee'
                        )">

                    <div id="dependent_employee_results"></div>

                    <input type="hidden" name="dependent_employee_no"
                        id="dependent_employee_no">

                    <p id="selected_dependent_employee">

                        No employee selected.

                    </p>

                    <div id="dependent_list"></div>

                    <button type="button" onclick="add_dependent()">+</button>

                    <br><br>

                    <button type="submit" name="save_dependent">

                        Save Dependents

                    </button>

                </form>

            </section>


            <section id="educational_background_section">

                <h3>Educational Background</h3>

                <form action="employee.php" method="post"
                    onsubmit="return check_employee('education_employee_no') && confirm('Save educational background?');">

                    <label>Search Employee</label>

                    <input type="text" id="education_employee_search"
                        oninput="search_employee(
                            'education_employee_search',
                            'education_employee_results',
                            'education_employee_no',
                            'selected_education_employee'
                        )">

                    <div id="education_employee_results"></div>

                    <input type="hidden" name="education_employee_no"
                        id="education_employee_no">

                    <p id="selected_education_employee">

                        No employee selected.

                    </p>

                    <div id="educational_background_list"></div>

                    <button type="button"
                            onclick="add_educational_background()">+</button>

                    <br><br>

                    <button type="submit" name="save_education">

                        Save Educational Background

                    </button>

                </form>

            </section>


            <section id="character_reference_section">

                <h3>Character References</h3>

                <form action="employee.php" method="post"
                    onsubmit="return check_employee('reference_employee_no') && confirm('Save character references?');">

                    <label>Search Employee</label>

                    <input type="text" id="reference_employee_search"
                        oninput="search_employee(
                            'reference_employee_search',
                            'reference_employee_results',
                            'reference_employee_no',
                            'selected_reference_employee'
                        )">

                    <div id="reference_employee_results"></div>

                    <input type="hidden" name="reference_employee_no"
                        id="reference_employee_no">

                    <p id="selected_reference_employee">

                        No employee selected.

                    </p>

                    <div id="character_reference_list"></div>

                    <button type="button"
                            onclick="add_character_reference()">+</button>

                    <br><br>

                    <button type="submit" name="save_reference">

                        Save Character References

                    </button>

                </form>

            </section>


        </div>

        <div id="categories" class="main_nav">

            <h1>CATEGORY</h1>

            <nav>

                <a href="#" onclick="show_section('categories','department_section')">

                    Department

                </a>

                <a href="#" onclick="show_section('categories','position_section')">

                    Position

                </a>

            </nav>


            <?php
                $department_list = $conn->query(
                    "SELECT department_no, department_name, department_color
                    FROM payroll_departments
                    ORDER BY department_no"
                );
            ?>


            <section id="department_section">

                <form action="employee.php" method="post"
                    enctype="multipart/form-data"

                    onsubmit="return confirm('<?php echo $department_edit_mode ? "Are you sure you want to update this department?" : "Are you sure you want to add this department?"; ?>');">

                    <label for="new_department_no">Department Number</label>

                    <input type="text" id="new_department_no" name="new_department_no" required

                        value="<?php echo $department_edit_mode ? $department['department_no'] : ''; ?>" maxlength="30">

                    <label for="new_department_name">Department Name</label>

                    <input type="text" id="new_department_name" name="new_department_name" required

                        value="<?php echo $department_edit_mode ? $department['department_name'] : ''; ?>" maxlength="100">

                    <label for="department_logo">Department Logo</label>

                    <?php if ($department_edit_mode && !empty($department["department_logo"])) {
                        $department_logo_info = getimagesizefromstring($department["department_logo"]);
                        $department_logo_type = $department_logo_info ? image_type_to_mime_type($department_logo_info[2]) : "image/png";
                    ?>
                        <img
                            src="data:<?php echo $department_logo_type; ?>;base64,<?php echo base64_encode($department["department_logo"]); ?>"
                            alt="Current Department Logo"
                            style="max-width:150px; max-height:150px; display:block; margin-bottom:10px;"
                        >
                    <?php } ?>

                    <input
                        type="file"
                        id="department_logo"
                        name="department_logo"
                        accept="image/*"
                    >

                    <label for="department_color">Department Color</label>

                    <input
                        type="color"
                        id="department_color"
                        name="department_color"
                        value="<?php echo $department_edit_mode && !empty($department['department_color']) ? htmlspecialchars($department['department_color']) : '#0b145c'; ?>"
                    >


                    <?php if ($department_edit_mode) { ?>

                        <input type="hidden" name="old_department_no"

                        value="<?php echo $department['department_no']; ?>">

                    <?php } ?>


                    <?php if ($department_edit_mode) { ?>

                        <button type="submit" name="update_department">

                            Update Department

                        </button>

                    <?php } else { ?>

                        <button type="submit" name="new_department">

                            Save New Department

                        </button>

                    <?php } ?>

                </form>


                <table>

                    <tr>

                        <th>Department Name</th>

                        <th>Actions</th>

                    </tr>


                    <?php

                        while ($row = $department_list->fetch_assoc()) {

                            echo "<tr>

                                <td>$row[department_name]</td>

                                <td>

                                    <form action='employee.php' method='post' style='display:inline;'>

                                        <input type='hidden' name='department_no' value='$row[department_no]'>

                                        <button type='submit' name='delete_department'>X</button>

                                    </form>


                                    <form action='employee.php' method='post' style='display:inline;'>

                                        <input type='hidden' name='department_no' value='$row[department_no]'>

                                        <button type='submit' name='edit_department'>Edit</button>

                                    </form>

                                </td>

                            </tr>";

                        }

                    ?>

                </table>

            </section>


            <section id="position_section">

                <form action="employee.php" method="post"

                    onsubmit="return confirm('<?php echo $position_edit_mode ? "Are you sure you want to update this position?" : "Are you sure you want to add this position?"; ?>');">

                    <label for="new_position_no">Position Number</label>

                    <input type="text" id="new_position_no" name="new_position_no" required

                        value="<?php echo $position_edit_mode ? $position['position_no'] : ''; ?>" maxlength="30">

                    <label for="new_position_name">Position Name</label>

                    <input type="text" id="new_position_name" name="new_position_name" required

                        value="<?php echo $position_edit_mode ? $position['position_name'] : ''; ?>" maxlength="100">


                    <?php if ($position_edit_mode) { ?>

                        <input type="hidden" name="old_position_no"

                            value="<?php echo $position['position_no']; ?>">

                    <?php } ?>


                    <?php if ($position_edit_mode) { ?>

                        <button type="submit" name="update_position">

                            Update Position

                        </button>

                    <?php } else { ?>

                        <button type="submit" name="new_position">

                            Save New Position

                        </button>

                    <?php } ?>

                </form>


                <?php

                    $position_list = $conn->query(

                        "SELECT position_no, position_name

                         FROM payroll_positions

                         ORDER BY position_no"

                    );

                ?>


                <table>

                    <tr>

                        <th>Position Name</th>

                        <th>Actions</th>

                    </tr>


                    <?php

                        while ($row = $position_list->fetch_assoc()) {

                            echo "<tr>

                                <td>$row[position_name]</td>

                                <td>

                                    <form action='employee.php' method='post' style='display:inline;'>

                                        <input type='hidden' name='position_no' value='$row[position_no]'>

                                        <button type='submit' name='delete_position'

                                            onclick=\"return confirm('Are you sure you want to delete this position?');\">X</button>

                                    </form>


                                    <form action='employee.php' method='post' style='display:inline;'>

                                        <input type='hidden' name='position_no' value='$row[position_no]'>

                                        <button type='submit' name='edit_position'>Edit</button>

                                    </form>

                                </td>

                            </tr>";

                        }

                    ?>

                </table>

            </section>

        </div>

        <div id="reports" class="main_nav">
            <h1>REPORTS</h1>
        </div>

        <script>
            function show_main(main_id) {
                document.querySelectorAll(".main_nav").forEach(function(main) {
                    main.style.display = "none";
                });

                document.getElementById(main_id).style.display = "block";

                if (main_id === "employees")
                    show_section("employees","personal_info_section");

                if (main_id === "categories")
                    show_section("categories","department_section");
            }

            function show_section(container_id,section_id) {
                document.querySelectorAll("#"+container_id+" section").forEach(function(section) {
                    section.style.display = "none";
                });

                document.getElementById(section_id).style.display = "block";
            }

            function preview_image() {
                var file = document.getElementById("employee_photo").files[0];
                var preview = document.getElementById("photo_preview");

                if (file) {
                    preview.src = URL.createObjectURL(file);
                    preview.style.display = "block";
                }
            }

            function filter_employees() {
                let search = document.getElementById("employee_search").value.toLowerCase();
                let department = document.getElementById("department_filter").value;
                let position = document.getElementById("position_filter").value;

                document.querySelectorAll(".employee_row").forEach(function(row) {

                    let employeeNo = row.dataset.employeeNo.toLowerCase();
                    let lastName = row.dataset.lastName.toLowerCase();
                    let firstName = row.dataset.firstName.toLowerCase();
                    let middleName = row.dataset.middleName.toLowerCase();

                    let rowDepartment = row.dataset.department;
                    let rowPosition = row.dataset.position;

                    let matchesSearch =
                        employeeNo.includes(search) ||
                        lastName.includes(search) ||
                        firstName.includes(search) ||
                        middleName.includes(search);

                    let matchesDepartment =
                        department === "" || rowDepartment === department;

                    let matchesPosition =
                        position === "" || rowPosition === position;

                    if (matchesSearch && matchesDepartment && matchesPosition) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            function search_employee(search_id, results_id, hidden_id, selected_id) {
                let search = document.getElementById(search_id).value;
                let results = document.getElementById(results_id);

                if (search == "") {
                    results.innerHTML = "";
                    return;
                }

                fetch("employee.php?search_employee=" + encodeURIComponent(search))
                .then(response => response.json())
                .then(data => {

                    results.innerHTML = "";

                    data.forEach(function(employee) {
                        let button = document.createElement("button");
                        button.type = "button";

                        button.textContent =
                            employee.employee_no + " - " +
                            employee.first_name + " " +
                            employee.last_name;

                        button.onclick = function() {
                            document.getElementById(hidden_id).value =
                                employee.employee_no;

                            document.getElementById(selected_id).textContent =
                                "Selected Employee: " +
                                employee.first_name + " " +
                                employee.last_name +
                                " (" + employee.employee_no + ")";

                            results.innerHTML = "";
                            document.getElementById(search_id).value = "";
                        };


                        results.appendChild(button);

                        results.appendChild(document.createElement("br"));

                    });

                });

            }

            /* NEW: CHECK IF AN EMPLOYEE WAS SELECTED */

            function check_employee(employee_id) {
                var employee = document.getElementById(employee_id).value;

                if (employee == "") {
                    alert("No selected employee.");
                    return false;
                }

                return true;
            }

            var emergency_contact_list =
                document.getElementById("emergency_contact_list");

            var dependent_list =
                document.getElementById("dependent_list");

            var educational_background_list =
                document.getElementById("educational_background_list");

            var character_reference_list =
                document.getElementById("character_reference_list");

            function add_emergency_contact() {
                emergency_contact_list.insertAdjacentHTML("beforeend", `
                <div class="emergency_contact_entry">
                    <button type="button"
                    onclick="this.parentElement.remove()">−</button>

                    <label>Name</label>
                    <input type="text" name="emergency_name[]" required maxlength="150">

                    <label>Relationship</label>
                    <input type="text" name="emergency_relationship[]" required maxlength="100">

                    <label>Address</label>
                    <input type="text" name="emergency_address[]" required maxlength="500">

                    <label>Contact</label>
                    <input type="text" name="emergency_contact[]" required maxlength="30">
                </div>`);
            }

            function add_dependent() {
                dependent_list.insertAdjacentHTML("beforeend", `
                <div class="dependent_entry">
                    <button type="button"
                    onclick="this.parentElement.remove()">−</button>

                    <label>Name</label>
                    <input type="text" name="dependent_name[]" required maxlength="150">

                    <label>Birth Date</label>
                    <input type="date" name="dependent_birth_date[]" required>

                    <label>Relationship</label>
                    <input type="text" name="dependent_relationship[]" required maxlength="100">
                </div>`);
            }

            function add_educational_background() {
                educational_background_list.insertAdjacentHTML("beforeend", `
                <div class="educational_background_entry">
                    <button type="button"
                    onclick="this.parentElement.remove()">−</button>

                    <label>School</label>
                    <input type="text" name="school[]" required maxlength="200">

                    <label>School Address</label>
                    <input type="text" name="school_address[]" required maxlength="500">

                    <label>School Year</label>
                    <input type="text" name="school_year[]" required maxlength="20">

                    <label>Level</label>
                    <select name="education_level[]" required>
                        <option value="">Select Level</option>
                        <option value="Elementary">Elementary</option>
                        <option value="Junior High School">Junior High School</option>
                        <option value="Senior High School">Senior High School</option>
                        <option value="College">College</option>
                        <option value="Vocational">Vocational</option>
                        <option value="Graduate School">Graduate School</option>
                    </select>
                </div>`);
            }

            function add_character_reference() {
                character_reference_list.insertAdjacentHTML("beforeend", `
                <div class="character_reference_entry">
                    <button type="button"
                    onclick="this.parentElement.remove()">−</button>

                    <label>Name</label>
                    <input type="text" name="reference_name[]" required maxlength="150">

                    <label>Occupation</label>
                    <input type="text" name="reference_occupation[]" required maxlength="150">

                    <label>Address</label>
                    <input type="text" name="reference_address[]" required maxlength="500">

                    <label>Contact</label>
                    <input type="text" name="reference_contact[]" required maxlength="30">
                </div>`);
            }

            let main = new URLSearchParams(window.location.search).get("main");
            let section = new URLSearchParams(window.location.search).get("section");

            if (main) {
                show_main(main);

                if (section) {
                    show_section(main, section);
                }

            } else {
                <?php if ($department_edit_mode) { ?>

                    show_main("categories");
                    show_section("categories", "department_section");

                <?php } else if ($position_edit_mode) { ?>

                    show_main("categories");
                    show_section("categories", "position_section");

                <?php } else if ($employee_edit_mode) { ?>

                    show_main("employees");
                    show_section("employees", "personal_info_section");

                <?php } else { ?>

                    show_main("dashboard");

                <?php } ?>
            }

            function escape_html(value) {
                return String(value ?? "")
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function display_value(value) {
                return value === null || value === "" ? "N/A" : escape_html(value);
            }

            function add_popup_row(container, label, value) {
                container.insertAdjacentHTML("beforeend", `
                    <div class="employee_info_row">
                        <strong>${escape_html(label)}</strong>
                        <span>${display_value(value)}</span>
                    </div>
                `);
            }

            function add_popup_section(container, title) {
                container.insertAdjacentHTML("beforeend", `
                    <h3 class="employee_popup_section_title">${escape_html(title)}</h3>
                `);
            }

            function manage_employee(employee_no) {
                document.getElementById("popup_employee_no").value = employee_no;
                document.getElementById("delete_employee_no").value = employee_no;

                fetch("employee.php?manage_employee=" + encodeURIComponent(employee_no))
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert("Employee not found.");
                            return;
                        }

                        const employee = data.employee;
                        const content = document.getElementById("employee_popup_information");
                        content.innerHTML = "";

                        const fullName = [
                            employee.first_name,
                            employee.middle_name,
                            employee.last_name
                        ].filter(Boolean).join(" ");

                        content.insertAdjacentHTML("beforeend", `
                            <div class="employee_popup_summary">
                                <div class="employee_popup_photo">
                                    ${employee.photo
                                        ? `<img src="${escape_html(employee.photo)}" alt="Employee Photo">`
                                        : `<div class="employee_popup_no_photo">No Photo</div>`
                                    }
                                </div>
                                <div class="employee_popup_identity">
                                    <h3>${display_value(fullName)}</h3>
                                    <p>Employee No.: <strong>${display_value(employee.employee_no)}</strong></p>
                                    <div class="employee_popup_salary">
                                        <small>Salary</small>
                                        <strong>
                                            ${employee.salary !== null && employee.salary !== ""
                                                ? `₱${Number(employee.salary).toLocaleString("en-PH", {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                })} / ${display_value(employee.rate_type).toUpperCase()}`
                                                : "N/A"
                                            }
                                        </strong>
                                    </div>

                                    <div class="employee_popup_job">
                                        <div>
                                            <small>Department</small>
                                            <strong>${display_value(employee.department_name)}</strong>
                                        </div>
                                        <div>
                                            <small>Position</small>
                                            <strong>${display_value(employee.position_name)}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);

                        add_popup_section(content, "Basic Information");
                        const basicGrid = document.createElement("div");
                        basicGrid.className = "employee_popup_grid";
                        content.appendChild(basicGrid);
                        add_popup_row(basicGrid, "Last Name", employee.last_name);
                        add_popup_row(basicGrid, "First Name", employee.first_name);
                        add_popup_row(basicGrid, "Middle Name", employee.middle_name);
                        add_popup_row(basicGrid, "Gender", employee.gender);
                        add_popup_row(basicGrid, "Birth Date", employee.birth_date);
                        add_popup_row(basicGrid, "Civil Status", employee.civil_status);
                        add_popup_row(basicGrid, "Nationality", employee.nationality);
                        add_popup_row(basicGrid, "Religion", employee.religion);

                        add_popup_section(content, "Contact Information");
                        const contactGrid = document.createElement("div");
                        contactGrid.className = "employee_popup_grid";
                        content.appendChild(contactGrid);
                        add_popup_row(contactGrid, "Email", employee.email);
                        add_popup_row(contactGrid, "Contact No.", employee.contact_no);
                        add_popup_row(contactGrid, "Permanent Address", employee.permanent_address);
                        add_popup_row(contactGrid, "Present Address", employee.present_address);

                        add_popup_section(content, "Government / Financial Information");
                        const governmentGrid = document.createElement("div");
                        governmentGrid.className = "employee_popup_grid";
                        content.appendChild(governmentGrid);
                        add_popup_row(governmentGrid, "SSS No.", employee.sss_no);
                        add_popup_row(governmentGrid, "PhilHealth No.", employee.philhealth_no);
                        add_popup_row(governmentGrid, "Pag-IBIG No.", employee.pagibig_no);
                        add_popup_row(governmentGrid, "TIN", employee.tin_no);
                        add_popup_row(governmentGrid, "ATM No.", employee.atm_no);

                        add_popup_section(content, "Employment Information");
                        const employmentGrid = document.createElement("div");
                        employmentGrid.className = "employee_popup_grid";
                        content.appendChild(employmentGrid);
                        add_popup_row(employmentGrid, "Department", employee.department_name);
                        add_popup_row(employmentGrid, "Position", employee.position_name);
                        add_popup_row(employmentGrid, "Starting Date", employee.starting_day);
                        add_popup_row(employmentGrid, "Ending Date", employee.ending_day);

                        document.getElementById("employee_manage_popup").style.display = "flex";
                    })
                    .catch(error => {
                        console.error(error);
                        alert("Unable to load employee information.");
                    });
            }

            function close_employee_popup() {
                document.getElementById(
                    "employee_manage_popup"
                ).style.display = "none";
            }
        </script>

    </body>
</html>