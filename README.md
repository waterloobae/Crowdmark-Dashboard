# Crowdmark Dashboard

## Release Notes : version 0.1
API call for responses is made for each booklet, that created around 2,500 API calls. As a result of that, it take around 30 minutes to create one Assessment. /api/responses/*response_id* is used for this.

Instead of using /api/questions/question_id/responses. Multi curls for /api/booklets/booklet_id/responses are used. It reduces response time from 30 minutes to 2 minutes.

Page.php needs to be coded.

## Crowdmark API Endpoints

**GET courses**  
`https://app.crowdmark.com/api/courses?api_key=your_api_key`

**GET one course**  
`https://app.crowdmark.com/api/courses/{course id}?api_key=your_api_key`

**GET assessments of one course**  
`https://app.crowdmark.com/api/courses/{course id}/assessments?api_key=your_api_key`

**GET one assessment**  
`https://app.crowdmark.com/api/assessments/{assessment id}?api_key=your_api_key`

**GET all booklets from assessment (paged)**  
`https://app.crowdmark.com/api/assessments/{assessment id}/booklets?api_key=your_api_key`

**GET one booklet**  
`https://app.crowdmark.com/api/booklets/{booklet id}?api_key=your_api_key`

**GET one booklet**  
`https://app.crowdmark.com/api/booklets/{booklet id}?api_key=your_api_key`

**GET responses from one booklet**  
`https://app.crowdmark.com/api/booklets/{booklet id}/responses?api_key=your_api_key`

**GET pages from one booklet**  
`https://app.crowdmark.com/api/booklets/{booklet id}/pages?api_key=your_api_key`

**GET scores from response**  
`https://app.crowdmark.com/api/responses/{response id}/scores?api_key=your_api_key`

**GET pages from response**  
`https://app.crowdmark.com/api/responses/{response id}/pages?api_key=your_api_key`

**GET one question**  
`https://app.crowdmark.com/api/questions/{question id}?api_key=your_api_key`

**GET the second page of booklets**  
`https://app.crowdmark.com/api/assessments/{assessment id}/booklets?page%5Bnumber%5D=2&api_key=your_api_key`

## Class Dependency

```mermaid
graph TD
    n1["Dashboard"] --> n2["Crowdmark<br>"]
    n2 --> n3["Course"]
    n3 --> n4["Assessment"]

    %% Reordering Question, Booklet, and Grader under Assessment
    n4 --> n5["Question"]
    n4 --> n6["Booklet"]
    n4 --> n7["Grader"]

    %% Booklet connections
    n6 -- Pages without Responses --> n10["Page"]
    n6 --> n8["Response"]

    %% Response connections
    n8 --> n9["Page"]

    %% Question to Response
    %% n5 -- Times out for Big Assessment --> n8
```
It is possible to get Responses from Question, however, it times out for big Assessments.

