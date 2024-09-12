// Function to switch between tabs
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}

// Function to select a subject and display syllabus and competencies
function selectSubject(subjectCode, subjectName, buttonElement) {
  // Highlight the selected subject button
  document.querySelectorAll('.btnSubjects button').forEach(function(button) {
      button.classList.remove('selected-subject');
  });
  buttonElement.classList.add('selected-subject');

  // Store selected subject in sessionStorage
  sessionStorage.setItem('selectedSubjectCode', subjectCode);
  sessionStorage.setItem('selectedSubjectName', subjectName);

  // Display the plan cards for Syllabus and Competencies
  document.getElementById('syllabusCard').style.display = 'block';
  document.getElementById('competenciesCard').style.display = 'block';

  // Update the competencies link with the selected subject
  document.getElementById('competenciesLink').href = 'view_competencies.php?subject_code=' + subjectCode;
}

// Initialize the page and auto-select the previously selected subject
document.addEventListener('DOMContentLoaded', function() {
  // Check if a subject was selected before
  var selectedSubjectCode = sessionStorage.getItem('selectedSubjectCode');
  var selectedSubjectName = sessionStorage.getItem('selectedSubjectName');

  if (selectedSubjectCode && selectedSubjectName) {
      // Auto-select the subject if previously selected
      var subjectButton = document.querySelector(`.btnSubjects button[onclick*="${selectedSubjectCode}"]`);
      if (subjectButton) {
          selectSubject(selectedSubjectCode, selectedSubjectName, subjectButton);
      }
  }
});
        // Function to display competencies count and show plans
        function selectSubject(subjectCode, buttonElement) {
          // Highlight the selected subject button
          document.querySelectorAll('.btnSubjects button').forEach(function(button) {
              button.classList.remove('selected-subject');
          });
          buttonElement.classList.add('selected-subject');

          // Show plans and update competencies link
          document.getElementById('syllabusCard').style.display = 'block';
          document.getElementById('competenciesCard').style.display = 'block';
          document.getElementById('competenciesLink').href = 'view_competencies.php?subject_code=' + subjectCode;

          // Fetch the competencies count for the selected subject
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'display_total_comp.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onload = function () {
              if (xhr.status === 200) {
                  var response = JSON.parse(xhr.responseText);
                  document.getElementById('competenciesCount').textContent = response.subject_competencies + ' out of ' + response.total_competencies;
              }
          };
          xhr.send('subject_code=' + subjectCode);
      }