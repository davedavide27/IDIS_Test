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

// Function to select a subject and fetch its syllabus and competencies
function selectSubject(subjectCode, subjectName, buttonElement) {
  // Highlight the selected subject button
  document.querySelectorAll('.btnSubjects button').forEach(function(button) {
      button.classList.remove('selected-subject');
  });
  buttonElement.classList.add('selected-subject');

  // Store the selected subject code and name in sessionStorage
  sessionStorage.setItem('selectedSubjectCode', subjectCode);
  sessionStorage.setItem('selectedSubjectName', subjectName);

  // Fetch syllabus and competencies for the selected subject
  fetchSyllabus(subjectCode, subjectName);
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

// Function to fetch syllabus for the selected subject
function fetchSyllabus(subjectCode, subjectName) {
  const request = new XMLHttpRequest();
  request.open('POST', 'fetch_syllabus.php', true);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  request.onload = function() {
    if (this.status === 200) {
      try {
        const response = JSON.parse(this.responseText);

        if (response.error) {
          console.error(response.error);
          return;
        }

        // Populate syllabus fields with response data
        document.querySelector('#courseCode').textContent = subjectCode;
        document.querySelector('#courseName').textContent = subjectName;
        document.querySelector('#courseUnits').textContent = response.course_units;
        document.querySelector('#courseDescription').textContent = response.course_description;
        document.querySelector('#prerequisites').textContent = response.prerequisites_corequisites;
        document.querySelector('#contactHours').textContent = response.contact_hours;
        document.querySelector('#performanceTasks').textContent = response.performance_tasks;

        // Populate PILO-GILO table
        const piloGiloTable = document.querySelector('#piloGiloTable tbody');
        piloGiloTable.innerHTML = ''; // Clear the table first
        response.pilo_gilo.forEach(function(mapping) {
          const row = `<tr><td>${mapping.pilo}</td><td>${mapping.gilo}</td></tr>`;
          piloGiloTable.innerHTML += row;
        });

        // Populate CILO-GILO table
        const ciloGiloTable = document.querySelector('#ciloGiloTable tbody');
        ciloGiloTable.innerHTML = ''; // Clear the table first
        response.cilos.forEach(function(cilo) {
          const row = `<tr><td>${cilo.description}</td><td>${cilo.gilo1}</td><td>${cilo.gilo2}</td></tr>`;
          ciloGiloTable.innerHTML += row;
        });

        // Populate context table
        const contextTable = document.querySelector('#contextTable tbody');
        contextTable.innerHTML = ''; // Clear the table first
        response.context.forEach(function(contextItem) {
          const row = `
            <tr>
              <td>${contextItem.section}</td>
              <td>${contextItem.hours}</td>
              <td>${contextItem.ilo}</td>
              <td>${contextItem.topics}</td>
              <td>${contextItem.institutional_values}</td>
              <td>${contextItem.teaching_activities}</td>
              <td>${contextItem.resources}</td>
              <td>${contextItem.assessment}</td>
              <td>${contextItem.course_map}</td>
            </tr>`;
          contextTable.innerHTML += row;
        });

      } catch (e) {
        console.error("Error parsing JSON response:", e);
      }
    } else {
      console.error("Failed to fetch syllabus. Status:", this.status);
    }
  };

  request.onerror = function() {
    console.error("Network error while fetching syllabus.");
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

  // Automatically load syllabus and competencies if a subject is already selected
  const selectedSubjectCode = sessionStorage.getItem('selectedSubjectCode');
  const selectedSubjectName = sessionStorage.getItem('selectedSubjectName');
  if (selectedSubjectCode && selectedSubjectName) {
    fetchSyllabus(selectedSubjectCode, selectedSubjectName);
    fetchCompetencies(selectedSubjectCode, selectedSubjectName);
  }
});

function printSyllabus() {
  // Retrieve subject code and name from sessionStorage
  const subjectCode = sessionStorage.getItem('selectedSubjectCode');
  const subjectName = sessionStorage.getItem('selectedSubjectName');

  console.log("Stored Subject Code:", subjectCode);
  console.log("Stored Subject Name:", subjectName);

  if (subjectCode && subjectName) {
      // Construct the URL with query parameters
      const syllabusUrl = `print_syllabus.php?subject_code=${encodeURIComponent(subjectCode)}&subject_name=${encodeURIComponent(subjectName)}`;
      console.log("Navigating to URL:", syllabusUrl);  // Log the URL

      // Navigate to the constructed URL
      window.location.href = syllabusUrl;
  } else {
      alert('Subject code or name not found. Please select a subject first.');
  }
}