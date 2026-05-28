<?php

namespace models;

class CourseSelectionSubmission
{
    public ?int $StudentIndex;
    public ?int $GradeLevel;
    public array $Courses;
    public array $Studies;

    public function __construct()
    {
        $this->StudentIndex = null;
        $this->GradeLevel = null;
        $this->Courses = [];
        $this->Studies = [];
    }
}