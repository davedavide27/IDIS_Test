// clickalbe tabs
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

// Function to handle subject selection and highlighting the selected button
function selectSubject(buttonElement, instructorName) {
  // Update the instructor's name in the <ul> dynamically
  document.getElementById("assignedInstructor").innerHTML =
    "Instructor Assigned: " + instructorName;
  // Remove the 'selected-subject' class from all buttons
  const subjectButtons = document.querySelectorAll(".btnSubjects button");
  subjectButtons.forEach(function (btn) {
    btn.classList.remove("selected-subject");
  });
  // Add the 'selected-subject' class to the clicked button
  buttonElement.classList.add("selected-subject");

  // Get the subject code from the button's text
  const subjectCode = buttonElement.textContent.match(/\(([^)]+)\)/)[1];

  // Clear previous ILOs and Topics data before fetching new data
  clearTable("#ILOs .remarksTable tbody");
  clearTable("#Topics .remarksTable tbody");

  buttonElement.classList.add("selected-subject");
  // Fetch the ILOs and Topics for the selected subject using AJAX
  fetchILOs(subjectCode);
  fetchTopics(subjectCode); // Add this function to fetch topics
}

// Function to fetch ILOs for the selected subject via AJAX
function fetchILOs(subjectCode) {
  const formData = new FormData();
  formData.append("subject_code", subjectCode);

  fetch("fetch_ilos.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      appendILOsToTable(data, subjectCode);
    })
    .catch((error) => console.error("Error fetching ILOs:", error));
}

// Function to fetch Topics for the selected subject via AJAX
function fetchTopics(subjectCode) {
  const formData = new FormData();
  formData.append("subject_code", subjectCode);

  fetch("fetch_topics.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      appendTopicsToTable(data, subjectCode);
    })
    .catch((error) => console.error("Error fetching topics:", error));
}

// Function to clear the table content
function clearTable(tableSelector) {
  const tableBody = document.querySelector(tableSelector);
  tableBody.innerHTML = ""; // Clear the table content
}

// Function to append the ILOs to the existing table without overriding
function appendILOsToTable(data, subjectCode) {
  const tableBody = document.querySelector("#ILOs .remarksTable tbody");

  // Clear existing content
  tableBody.innerHTML = "";

  if (data.message) {
    // If there's a message (e.g., no approved ILOs), display it
    const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
    tableBody.innerHTML = noDataRow;
  } else {
    // Iterate over each section and append the ILOs
    let hasData = false;

    ["PRELIM", "MIDTERM", "SEMIFINAL", "FINAL"].forEach((section) => {
      if (data[section] && data[section].length > 0) {
        hasData = true;

        data[section].forEach((ilo) => {
          const row = `
                    <tr data-ilo="${ilo}_${section}">
                        <td>${ilo} (${section})</td>
                        <td>
                            <input type="text" placeholder="Enter comments..." class="ilo-comment">
                            <button onclick="submitComment('${subjectCode}', '${ilo}', this)">Submit</button>
                        </td>
                    </tr>`;
          tableBody.innerHTML += row; // Append row without clearing the table
        });
      }
    });

    // If there's no ILO data, display a "No data" message
    if (!hasData) {
      const noDataRow = `<tr><td colspan="2" class="no-data-message">No ILOs available for the selected subject.</td></tr>`;
      tableBody.innerHTML = noDataRow;
    }
  }
}
// Embed CSS styles directly into the JavaScript
const style = document.createElement("style");
style.innerHTML = `
    /* General styling for the table */
    .remarksTable {
        width: 100%; /* Adjust the table width */
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #f9f9f9;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Styling for the table headers */
    .remarksTable thead th {
        background-color: black;
        color: white;
        padding: 12px;
        font-size: 16px;
        text-align: center;
        border: 1px solid white;
    }

    /* Styling for table rows and cells */
    .remarksTable tbody td {
        padding: 5px;
        border: 1px solid #ddd;
        font-size: 20px;
        text-align: center;
    }

    /* Styling for radio inputs */
    .remarksTable tbody input[type="radio"] {
        margin-left: 5px;
        margin-right: 5px;
    }

    /* Submit button styling */
    #submitRatingsButton {
        display: block;
        margin: 20px auto;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        border-radius: 5px;
        transition: background-color 0.3s;
        text-align: center;
        
    }

    #submitRatingsButton:hover {
        background-color: #45a049;
    }
`;
document.head.appendChild(style);

// Function to append the Topics to the existing table without overriding
function appendTopicsToTable(data, subjectCode) {
  const tableBody = document.querySelector("#Topics .remarksTable tbody");

  // Clear existing content
  tableBody.innerHTML = "";

  if (data.message) {
    // If there's a message (e.g., no approved topics), display it
    const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
    tableBody.innerHTML = noDataRow;
  } else {
    // Iterate over each section and append the topics
    let hasData = false;

    ["PRELIM", "MIDTERM", "SEMIFINAL", "FINAL"].forEach((section) => {
      if (data[section] && data[section].length > 0) {
        hasData = true;

        data[section].forEach((topic) => {
          const row = `
                <tr data-topic="${topic}_${section}">
                    <td>${topic} (${section})</td>
                    <td>
                        <input type="radio" name="${topic}_${section}_rating" value="1">1
                        <input type="radio" name="${topic}_${section}_rating" value="2">2
                        <input type="radio" name="${topic}_${section}_rating" value="3">3
                        <input type="radio" name="${topic}_${section}_rating" value="4">4
                        <input type="radio" name="${topic}_${section}_rating" value="5">5
                    </td>
                </tr>`;
          tableBody.innerHTML += row; // Append row without clearing the table
        });
      }
    });

    // If there's no topic data, display a "No data" message
    if (!hasData) {
      const noDataRow = `<tr><td colspan="2" class="no-data-message">No topics available for the selected subject.</td></tr>`;
      tableBody.innerHTML = noDataRow;
    }

    // Append the submit button after the table rows
    const submitButtonRow = `
        <tr>
            <td colspan="2">
                <button id="submitRatingsButton" onclick="submitAllRatings('${subjectCode}')">Submit All Ratings</button>
            </td>
        </tr>`;
    tableBody.innerHTML += submitButtonRow;
  }
}



// Function to submit a comment for an ILO
function submitComment(subjectCode, ilo, buttonElement) {
  const commentInput = buttonElement.previousElementSibling; // Get the comment input
  const comment = commentInput.value.trim(); // Get and trim the comment input

  if (comment === "") {
    alert("Please enter a comment.");
    return;
  }

  const formData = new FormData();
  formData.append("subject_code", subjectCode);
  formData.append("ilo", ilo);
  formData.append("comment", comment);

  fetch("submit_comment.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Comment submitted successfully.");
        commentInput.value = ""; // Clear the comment field
      } else {
        alert("Failed to submit comment.");
      }
    })
    .catch((error) => console.error("Error submitting comment:", error));
}

// Function to submit all ratings for the topics in a single submission
function submitAllRatings(subjectCode) {
    // Create an object to store the ratings for each topic
    const ratings = {};

    // Select all topic rating inputs
    const topicRows = document.querySelectorAll('.remarksTable tbody tr');

    // Loop through each row and get the selected rating for each topic
    topicRows.forEach(row => {
        const topic = row.getAttribute('data-topic'); // Get the topic from the row
        const selectedRating = row.querySelector(`input[name="${topic}_rating"]:checked`); // Get the selected rating

        if (selectedRating) {
            ratings[topic] = selectedRating.value; // Store the selected rating for each topic
        }
    });

    // Check if at least one rating is selected
    if (Object.keys(ratings).length === 0) {
        alert("Please select at least one rating before submitting.");
        return;
    }

    // Prepare the form data
    const formData = new FormData();
    formData.append('subject_code', subjectCode);
    formData.append('ratings', JSON.stringify(ratings)); // Send ratings as JSON

    // Submit all ratings at once via fetch
    fetch('submit_rating.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${data.message}`);
            // Optionally reset or uncheck all ratings after successful submission
            document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                input.checked = false;
            });
        } else {
            alert('Failed to submit some or all ratings.');
        }
    })
    .catch(error => console.error('Error submitting ratings:', error));
}