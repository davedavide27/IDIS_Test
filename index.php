<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Competencies</title>
</head>
<body>
    <h3>Competency Implementation</h3>
    <table>
        <tr>
            <td><h4>I. Subject code</h4></td>
            <td>: <input type="text"></td>
        </tr>
        <tr>
            <td><h4>II. Subject title</h4></th>
            <td>: <input type="text" style="width: 300pt;"></td>
        </tr>
        <tr>
            <td><h4>III. Units</h4></td>
            <td>: <input type="number"></td>
        </tr>
        <tr>
            <td><h4>IV. Hours</h4></td>
            <td>: <input type="number"></td>
        </tr>
        <tr>
            <td><h4>V. Department</h4></td>
            <td>: <select name="" id="">
                <option value="">COLLEGE OF ARTS AND SCIENCES</option>
            </select></td>
        </tr>
        <tr>
            <td><h4>VI. School year</h4></td>
            <td>: <input type="number" style="width: 10%;">- <input type="number" style="width: 10%;"></td>
        </tr>
        <tr>
            <td><h4>VII. Grading period/quarter</h4></td>
            <td>: 
                <select name="" id="">
                    <option value="">1st SEMESTER</option>
                    <option value="">2nd SEMESTER</option>
                </select>
                <select name="" id="">
                    <option value="">PRELIM</option>
                    <option value="">MIDTERM</option>
                    <option value="">SEMI-FINAL</option>
                    <option value="">FINAL</option>
                </select>
            -
                <select name="" id="">
                    <option value="">PRELIM</option>
                    <option value="">MIDTERM</option>
                    <option value="">SEMI-FINAL</option>
                    <option value="">FINAL</option>
                </select>
        </td>
        </tr>
        <tr>
            <td><h4>VIII. Competencies</h4></td>
            <td>:</td>
        </tr>
    </table>
    <table class="table2">
        <tr>
            <th>SMCC Competencies</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>1. Discuss the school's vision mission, objectives, core values, and Michealinian identity; <input type="text"></td>
            <td>
                <select name="" id=""  value=""  style="font-size: large;">
                    <option value=""></option>
                    <option>IMPLEMENTED</option>
                    <option value="">NOT IMPLEMENTED</option>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td  style="text-align: center;"><button>+</button></td>
        </tr>
    </table>

       <li>Total number of Competencies DepEd/TESDA/CHED: <input type="number" name="" id=""></li>
       <li>Total Number of Competencies SMCC based on DepEd?TESDA?CHED: <input type="number" name="" id=""></li>
       <li>Total Number of Institutional Competencies: <input type="number" name="" id=""></li>
       <li>Total Number of B and C: <input type="number" name="" id=""></li>
       <li>Total Number of Competencies Implemented: <input type="number" name="" id=""></li>
       <li>Total Number of Competencies NOT Implemented: <input type="number" name="" id=""></li>
       <li>% Number of Competencies Implemented: <input type="number"></li>
    
    <br>
    <p>Prepared by: </p>
    <br>
    <br>
    <p>Checked by: </p>
    <br>
    <br>
    <p>Noted by: </p>

    <button>save edits</button>
    <button onclick="window.print()">Print this page</button>
</body>
</html>