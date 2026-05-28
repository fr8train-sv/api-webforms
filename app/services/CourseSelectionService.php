<?php

namespace services;

use Exception;
use factories\ObjectFactory;
use models\BasicCourseSelectionRequest;
use models\CourseSelectionSubmission;
use models\ServiceResponse;
use PDO;

class CourseSelectionService
{
    protected PDO $appDB;
    protected PDO $dw;

    public function __construct(PDO $appDB, PDO $dw)
    {
        $this->appDB = $appDB;
        $this->dw = $dw;
    }

    public function queryPriorSubmission(BasicCourseSelectionRequest $request): ServiceResponse
    {
        if (empty($request->StudentIndex)) {
            return ServiceResponse::cast([
                'http_code' => 400,
                'message' => 'StudentIndex is required',
            ]);
        }

        if (empty($request->GradeLevel)) {
            return ServiceResponse::cast([
                'http_code' => 400,
                'message' => 'GradeLevel is required',
            ]);
        }

        $stmt = $this->appDB->prepare("select *
from webforms.course_selections
where StudentIndex = ? and GradeLevel = ?");
        $stmt2 = $this->appDB->prepare("select * from webforms.course_studies where StudentIndex = ? and GradeLevel = ?");

        try {
            $stmt->execute([
                $request->StudentIndex,
                $request->GradeLevel,
            ]);
            $stmt2->execute([
                $request->StudentIndex,
                $request->GradeLevel,
            ]);
        } catch (Exception $e) {
            return ServiceResponse::cast([
                'http_code' => 500,
                'message' => 'Failed to fetch prior submissions: '.$e->getMessage(),
                'exception' => $e,
            ]);
        }

        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $studies = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return ServiceResponse::cast([
            'message' => 'Priors fetched successfully',
            'payload' => [
                'submissions' => $submissions,
                'studies' => $studies,
            ]
        ]);
    }

    public function deletePriorSubmission(BasicCourseSelectionRequest $request): ServiceResponse
    {
        if (empty($request->StudentIndex)) {
            return ServiceResponse::cast([
                'http_code' => 400,
                'message' => 'StudentIndex is required',
            ]);
        }

        if (empty($request->GradeLevel)) {
            return ServiceResponse::cast([
                'http_code' => 400,
                'message' => 'GradeLevel is required',
            ]);
        }

        $stmt = $this->appDB->prepare("delete from webforms.course_selections where StudentIndex = ? and GradeLevel = ?");
        $stmt2 = $this->appDB->prepare("delete from webforms.course_studies where StudentIndex = ? and GradeLevel = ?");
        try {
            $stmt->execute([$request->StudentIndex, $request->GradeLevel]);
            $stmt2->execute([$request->StudentIndex, $request->GradeLevel]);
        } catch (Exception $e) {
            return ServiceResponse::cast([
                'http_code' => 500,
                'message' => 'Failed to delete prior submission: '.$e->getMessage(),
                'exception' => $e,
            ]);
        }

        return ServiceResponse::cast([
            'message' => 'Prior submission deleted successfully',
        ]);
    }

    public function storeSubmission(CourseSelectionSubmission $submission): ServiceResponse
    {
        $serviceResponse = $this->deletePriorSubmission(ObjectFactory::loadClass(BasicCourseSelectionRequest::class, (array) $submission));
        if ($serviceResponse->http_code !== 200) {
            return $serviceResponse;
        }

        try {
            foreach ($submission->Courses as $course) {
                $stmt = $this->appDB->prepare("insert into webforms.course_selections (StudentIndex, GradeLevel, CourseName) values (?, ?, ?)");
                $stmt->execute([$submission->StudentIndex, $submission->GradeLevel, $course]);
            }

            foreach ($submission->Studies as $study) {
                $stmt = $this->appDB->prepare("insert into webforms.course_studies (StudentIndex, GradeLevel, StudyName) values (?, ?, ?)");
                $stmt->execute([$submission->StudentIndex, $submission->GradeLevel, $study]);
            }
        } catch (Exception $e) {
            return ServiceResponse::cast([
                'http_code' => 500,
                'message' => 'Failed to store submission: '.$e->getMessage(),
                'exception' => $e,
            ]);
        }
        return ServiceResponse::cast([
            'message' => 'Submission stored successfully',
        ]);
    }

    public function fetchStudent(BasicCourseSelectionRequest $request): ServiceResponse
    {
        if (!empty($request->StudentIndex)) {
            $stmt = $this->dw->prepare("select *
from bronze_genius.students
where StudentIndex = ?");
            $stmt->execute([$request->StudentIndex]);
        } elseif (!empty($request->Email)) {
            $stmt = $this->dw->prepare("select *
from bronze_genius.students
where Email = ?");
            $stmt->execute([$request->Email]);
        } else {
            return ServiceResponse::cast([
                'http_code' => 400,
                'message' => 'Invalid request parameters',
            ]);
        }

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return ServiceResponse::cast([
                'http_code' => 404,
                'message' => 'Student not found',
            ]);
        }

        return ServiceResponse::cast([
            'message' => 'Student fetched successfully',
            'payload' => [
                'student' => $student,
            ]
        ]);
    }
}