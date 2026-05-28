<?php

namespace models;

class BasicCourseSelectionRequest
{
    public ?int $StudentIndex;
    public ?string $Email;
    public ?int $GradeLevel;

    public function __construct()
    {
        $this->StudentIndex = null;
        $this->Email = null;
        $this->GradeLevel = null;
    }
}