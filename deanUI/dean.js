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

  // Store the selected subject code and name in sessionStorage
  sessionStorage.setItem('selectedSubjectCode', subjectCode);
  sessionStorage.setItem('selectedSubjectName', subjectName);

  // Fetch and display competencies for the selected subject
  fetchCompetencies(subjectCode, subjectName);
}

// Function to fetch competencies for the selected subject
function fetchCompetencies(subjectCode, subjectName) {
  const request = new XMLHttpRequest();
  request.open('POST', 'competencies.php', true);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  request.onload = function() {
    if (this.status === 200) {
      try {
        const response = JSON.parse(this.responseText);
        const tableBody = document.querySelector('#competenciesTable tbody');

        // Clear existing rows, except the first header row
        tableBody.innerHTML = '';

        // Check if competencies were found
        if (response.length > 0) {
          response.forEach(function(competency) {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${competency.competency_description}</td>
              <td>${competency.remarks === 'IMPLEMENTED' ? 'IMPLEMENTED' : 'NOT IMPLEMENTED'}</td>
            `;
            tableBody.appendChild(row);
          });
        } else {
          const row = document.createElement('tr');
          row.innerHTML = `<td colspan="2">No competencies found for this subject.</td>`;
          tableBody.appendChild(row);
        }
      } catch (e) {
        console.error("Error parsing JSON response:", e);
      }
    } else {
      console.error("Failed to fetch competencies. Status:", this.status);
    }
  };

  request.onerror = function() {
    console.error("Network error while fetching competencies.");
  };

  request.send(`subject_code=${encodeURIComponent(subjectCode)}`);
}

// Wait until the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  const instructorForm = document.getElementById('instructorForm');

  // Only add the event listener if the form exists in the DOM
  if (instructorForm) {
    instructorForm.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent the default form submission

      var formData = new FormData(this);

      fetch('', { // Send the request to the same page
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // Optionally, update the page with the new data without reloading
        document.body.innerHTML = data;
      })
      .catch(error => console.error('Error:', error));
    });
  }

  // Automatically load competencies if a subject is already selected
  const selectedSubjectCode = sessionStorage.getItem('selectedSubjectCode');
  const selectedSubjectName = sessionStorage.getItem('selectedSubjectName');
  if (selectedSubjectCode && selectedSubjectName) {
    fetchCompetencies(selectedSubjectCode, selectedSubjectName);
  }
});
