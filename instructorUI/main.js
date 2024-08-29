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

// Function to select a subject and fetch its competencies
function selectSubject(subjectCode, subjectName, buttonElement) {
  // Highlight the selected subject button
  document.querySelectorAll('.btnSubjects button').forEach(function(button) {
      button.classList.remove('selected-subject');
  });
  buttonElement.classList.add('selected-subject');

  // Store selected subject in sessionStorage
  sessionStorage.setItem('selectedSubjectCode', subjectCode);
  sessionStorage.setItem('selectedSubjectName', subjectName);
  
  // Update hidden input fields in the Competencies form
  document.getElementById('selected_subject_code').value = subjectCode;
  document.getElementById('selected_subject_name').value = subjectName;

  // Filter content by selected subject
  filterContentBySubject(subjectCode);

  // Fetch and display competencies for the selected subject
  fetchCompetencies(subjectCode);
}

// Function to filter content by selected subject
function filterContentBySubject(subjectCode) {
  // Update Plan Cards
  document.querySelectorAll('.planCard').forEach(function(card) {
      if (card.getAttribute('data-subject-code') === subjectCode || card.getAttribute('data-subject-code') === '') {
          card.style.display = 'block';
      } else {
          card.style.display = 'none';
      }
  });

  // Update Competency Items
  document.querySelectorAll('.remarksTable').forEach(function(table) {
      if (table.getAttribute('data-subject-code') === subjectCode || table.getAttribute('data-subject-code') === '') {
          table.style.display = 'table';
      } else {
          table.style.display = 'none';
      }
  });

  // Update Comments
  document.querySelectorAll('.commentCard').forEach(function(card) {
      if (card.closest('#containerComment').getAttribute('data-subject-code') === subjectCode || card.closest('#containerComment').getAttribute('data-subject-code') === '') {
          card.style.display = 'block';
      } else {
          card.style.display = 'none';
      }
  });
}

// Function to fetch competencies for a selected subject
function fetchCompetencies(subjectCode) {
  const request = new XMLHttpRequest();
  request.open('GET', `fetch_competencies.php?subject_code=${subjectCode}`, true);
  request.onload = function() {
      if (this.status === 200) {
          const response = JSON.parse(this.responseText);
          const tableBody = document.querySelector('.remarksTable tbody');
          tableBody.innerHTML = '';

          if (response.length > 0) {
              response.forEach(function(competency) {
                  const row = document.createElement('tr');
                  row.innerHTML = `
                      <td>${competency.competency_description}</td>
                      <td class="inputCheck"><input type="checkbox" ${competency.remarks === 'IMPLEMENTED' ? 'checked' : ''}></td>
                  `;
                  tableBody.appendChild(row);
              });
          } else {
              const noCompetenciesRow = document.createElement('tr');
              noCompetenciesRow.innerHTML = `<td colspan="2">No competencies found for this subject.</td>`;
              tableBody.appendChild(noCompetenciesRow);
          }
      }
  };
  request.send();
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
