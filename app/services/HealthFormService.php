<?php

namespace services;

use models\HealthFormInput;
use models\ServiceResponse;
use PDO;

class HealthFormService
{
    protected PDO $appDB;
    protected PDO $dw;

    public function __construct(PDO $appDB, PDO $dw)
    {
        $this->appDB = $appDB;
        $this->dw = $dw;
    }

    public function saveHealthForm(HealthFormInput $input): ServiceResponse
    {
        # ATTEMPT TO IDENTIFY STUDENT INDEX BY EMAIL
        try {
            $sql = <<<SQL
select StudentIndex
from bronze_genius.students
where Email = :email
SQL;
            $stmt = $this->dw->prepare($sql);
            $stmt->bindParam(':email', $input->student->email, PDO::PARAM_STR);
            $stmt->execute();
            $studentIndex = $stmt->fetchColumn();

            if (!empty($studentIndex)) $studentIndex = intval($studentIndex);
        } catch (\Exception $e) {
            # DON'T HAVE TO ACTUALLY THROW ERROR HERE
            # USEFUL FOR DATA CONTINUITY BUT NOT CRITICAL
        }

        # SAVE ALL DATA TO APP DB
        try {
            $sql = <<<SQL
insert into webforms.health_assessment (student_index, 
                                        email, 
                                        firstname, 
                                        lastname, 
                                        weight, 
                                        height_feet, 
                                        height_inches,
                                        one_mile_run_minutes, 
                                        one_mile_run_seconds, 
                                        curl_ups, 
                                        trunk_lift, 
                                        push_ups,
                                        shoulder_stretch_left, 
                                        shoulder_stretch_right, 
                                        assessment_date)
values (:studentIndex, :email, :firstname, :lastname, :weight, :heightFeet, :heightInches, :oneMileRunMinutes, :oneMileRunSeconds, :curlUps, :trunkLift, :pushUps, :shoulderStretchLeft, :shoulderStretchRight, :assessmentDate)
SQL;
            $stmt = $this->appDB->prepare($sql);
            $studentIndexValue = $studentIndex ?: null;
            $stmt->bindValue(':studentIndex',
                $studentIndex,
                $studentIndexValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':email', $input->student->email, PDO::PARAM_STR);
            $stmt->bindParam(':firstname', $input->student->firstname, PDO::PARAM_STR);
            $stmt->bindParam(':lastname', $input->student->lastname, PDO::PARAM_STR);
            $stmt->bindParam(':weight', $input->student->weight, PDO::PARAM_INT);
            $stmt->bindParam(':heightFeet', $input->student->height->feet, PDO::PARAM_INT);
            $stmt->bindParam(':heightInches', $input->student->height->inches, PDO::PARAM_INT);
            $stmt->bindParam(':oneMileRunMinutes', $input->assessment->one_mile_run->minutes, PDO::PARAM_INT);
            $stmt->bindParam(':oneMileRunSeconds', $input->assessment->one_mile_run->seconds, PDO::PARAM_INT);
            $stmt->bindParam(':curlUps', $input->assessment->curl_ups, PDO::PARAM_INT);
            $stmt->bindParam(':trunkLift', $input->assessment->trunk_lift, PDO::PARAM_INT);
            $stmt->bindParam(':pushUps', $input->assessment->push_ups, PDO::PARAM_INT);
            $stmt->bindParam(':shoulderStretchLeft', $input->assessment->shoulder_stretch->left, PDO::PARAM_BOOL);
            $stmt->bindParam(':shoulderStretchRight', $input->assessment->shoulder_stretch->right, PDO::PARAM_BOOL);
            $stmt->bindValue(':assessmentDate', $input->date->toDateString(), PDO::PARAM_STR);

            $stmt->execute();
        } catch (\Exception $e) {
            return ServiceResponse::cast([
                'http_code' => 500,
                'message' => 'Failed to save health form: '.$e->getMessage(),
            ]);
        }
        return ServiceResponse::cast([
            'message' => 'Health form saved successfully!'
        ]);
    }
}