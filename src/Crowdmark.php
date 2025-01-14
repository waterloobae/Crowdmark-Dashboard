<?php

namespace Waterloobae\CrowdmarkDashboard;
use Waterloobae\CrowdmarkDashboard\API;
use Waterloobae\CrowdmarkDashboard\Course;
use Waterloobae\CrowdmarkDashboard\Assessment;
use Waterloobae\CrowdmarkDashboard\Page;

use setasign\Fpdi\Fpdi;

class Crowdmark
{
    protected array $courses = [];
    protected array $course_ids = [];
    protected array $assessment_ids = [];

    protected object $api_response;

    public function __construct()
    {
        // constructor
        $api = new API();
        $api->exec('api/courses');
        $this->api_response = $api->getResponse();
        $course_data = array();
        foreach ($this->api_response->data as $course_data) {
            $this->courses[] = new Course($course_data->id);
            $this->course_ids[] = $course_data->id;
        }
        $this->setAssessmentIDs();
    }

    public function setAssessmentIDs()
    {
        foreach ($this->courses as $course) {
            $this->assessment_ids = array_merge($this->assessment_ids, $course->getAssessmentIds());
        }
    }   

    public function returnAssessmentIDs(array $course_names)
    {
        $assessment_ids = [];
        foreach($this->courses as $course) {
            if(in_array($course->getCourseName(), $course_names)) {
                $assessment_ids = array_merge($assessment_ids, $course->getAssessmentIds());
            }
        }
        return $assessment_ids;
    }

    public function createDownloadLinks(string $type, array $course_names, string $page_number = null)
    {
        if($type == "page") {
        foreach($course_names as $course_name) {
                $is_valid = false;

                foreach($this->courses as $course) {
                    if($course->getCourseName() == $course_name) {
                        $is_valid = true;
                        break;
                    }
                }

                $relativePath = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
                $encoded_course_name = urlencode($course_name);
                $download_link = $relativePath."/Download.php?type=" . $type . "&course_name=" . $encoded_course_name. "&page_number=" . $page_number;
                
                if($is_valid) {
                    echo '<a href="' . $download_link . '" download onclick="this.innerText=\'Loading '.$course_name.'...Please wait!\'; this.style.pointerEvents = \'none\';">Download PDF (' . $course_name . ')</a><br>';
                } else {
                    echo "Invalid course name: " . $course_name . "<br>";
                }

                // echo '<a href="'. $download_link . '" download>Download PDF ('.$course_name.') </a><br>';
            }
        }
    }

    public function downloadPagesByPageNumber(array $assessment_ids, string $page_number)
    {
        $assessments = [];
        foreach($assessment_ids as $assessment_id) {
            $temp = new Assessment($assessment_id);
            if($page_number == '1'){
                $temp->setCoverPages($temp->getBooklets());
            }else{
                $temp->setResponses($temp->getBooklets());
            }
            $assessments[] = $temp;
        }   

        $pageUrls = [];
        foreach($assessments as $assessment) {
            foreach($assessment->getBooklets() as $booklet) {
                if($page_number == '1'){
                    foreach($booklet->getPages() as $page) {
                        if($page->getPageNumber() == $page_number){
                            $pageUrls[] = $page->getPageUrl();
                        }
                    }
                }else{
                    foreach($booklet->getResponses() as $response) {
                        foreach($response->getPages() as $page) {
                            // Some of $page is not an instance of Page, but stdClass.
                            if ($page instanceof Page ) {
                                //error_log("Page Number: ".$page->getPageNumber());
                                if($page->getPageNumber() == $page_number){
                                    $pageUrls[] = $page->getPageUrl();
                                }
                            }
                        }
                    }
                }
            }
        }

        $pdf = new Fpdi();
        foreach ($pageUrls as $url) {
            // debug
            // error_log(substr($url, 0, 10));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Set timeout to 30 seconds
            $image = curl_exec($ch);

            curl_close($ch);
            $imagePath = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
            file_put_contents($imagePath, $image);

            list($width, $height) = getimagesize($imagePath);
            $pdf->AddPage('P', [$width, $height]);
            $pdf->Image($imagePath, 0, 0, $width, $height);
            unlink($imagePath);
        }

        $dateTime = date("Ymd_His");
        $fileName = "Page_".$page_number."_". $dateTime . ".pdf";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'. $fileName . '"');
        $pdf->Output($fileName, 'D');

        // $pdf->Output('F', sys_get_temp_dir() . "/cover_pages_" . $dateTime . ".pdf");
        // echo '<a href="'. sys_get_temp_dir() . $dateTime . '.pdf" download>Download PDF</a>';
    }
    
    public function getCourses()
    {
        return $this->courses;
    }

    public function getCourseIds()
    {
        return $this->course_ids;
    }

    public function getAPIResponse()
    {
        return $this->api_response;
    }

}

