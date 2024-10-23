// Clickable tabs
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
async function appendILOsToTable(data, subjectCode) {
  const tableBody = document.querySelector("#ILOs .remarksTable tbody");

  // Clear existing content
  tableBody.innerHTML = "";

  if (data.message) {
    // If there's a message (e.g., no approved ILOs), display it
    const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
    tableBody.innerHTML = noDataRow;
    disableSubmitButton(); // Disable submit buttons when there's no data
    return; // Stop further processing
  }

  // Iterate over each section and append the ILOs
// Iterate over each section and append the ILOs
// Iterate over each section and append the ILOs
let hasData = false;

for (const section of ["PRELIM", "MIDTERM", "SEMIFINAL", "FINAL"]) {
    if (data[section] && data[section].length > 0) {
        hasData = true;

        for (const ilo of data[section]) {
            const commentExists = await checkCommentExists(subjectCode, ilo); // Check if a comment exists

            // Create row with input and button
            const row = `
            <tr data-ilo="${ilo}_${section}">
                <td>${ilo} (${section})</td>
                <td>
                    <input type="text" 
                           placeholder="${commentExists ? 'Comments already Submitted' : 'Enter comments...'}" 
                           class="ilo-comment" 
                           style="width: 200px;" 
                           ${commentExists ? "disabled" : ""}><!-- Disable input if comment exists -->
                    <button style="${commentExists ? 'pointer-events: none; opacity: 0.5;' : ''}" 
                            onclick="submitComment('${subjectCode}', '${ilo}', this)" 
                            ${commentExists ? "disabled" : ""}>Submit</button>
                </td>
            </tr>`;
            tableBody.innerHTML += row; // Append row without clearing the table
        }
    }
}


  // If there's no ILO data, display a "No data" message
  if (!hasData) {
    const noDataRow = `<tr><td colspan="2" class="no-data-message">No ILOs available for the selected subject.</td></tr>`;
    tableBody.innerHTML = noDataRow;
    disableSubmitButton(); // Disable submit buttons when there's no ILO data
  } else {
    enableSubmitButtons(); // Ensure buttons are enabled if data exists
  }
}

// Function to check if a comment exists for a specific ILO
async function checkCommentExists(subjectCode, ilo) {
  console.log("Checking comment existence for:", {
    subject_code: subjectCode,
    ilo: ilo,
  }); // Debug log

  const response = await fetch("check_comments.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ subject_code: subjectCode, ilo: ilo }),
  });

  const data = await response.json();
  console.log("Response from check_comments.php:", data); // Debug log
  return data.exists; // Returns true or false based on comment existence
}

// Function to disable all submit buttons
function disableSubmitButton() {
  const submitButtons = document.querySelectorAll(".remarksTable button");
  submitButtons.forEach((button) => {
    button.disabled = true; // Disable all buttons
  });
}

// Function to enable all submit buttons
function enableSubmitButtons() {
  const submitButtons = document.querySelectorAll(".remarksTable button");
  submitButtons.forEach((button) => {
    button.disabled = false; // Enable all buttons
  });
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
        alert("Comment submitted successfully!");
        buttonElement.disabled = true; // Disable the submit button after submission
        commentInput.disabled = true; // Disable the comment input after submission
        commentInput.value = ""; // Clear the comment input
      } else {
        alert("Error submitting comment: " + data.message);
      }
    })
    .catch((error) => console.error("Error submitting comment:", error));
}

// Function to submit all ratings for the selected subject
function submitAllRatings(subjectCode) {
  const ratings = {};
  const ratingInputs = document.querySelectorAll(
    `#Topics .remarksTable input[type="radio"]:checked`
  );
  ratingInputs.forEach((input) => {
    const topic = input.name; // Get the name of the input (topic)
    const rating = input.value; // Get the selected rating value
    ratings[topic] = rating; // Store the topic and rating
  });

  // AJAX call to submit all ratings at once
  const formData = new FormData();
  formData.append("subject_code", subjectCode);
  formData.append("ratings", JSON.stringify(ratings));

  fetch("submit_ratings.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Ratings submitted successfully!");
        document
          .querySelectorAll(`#Topics .remarksTable input[type="radio"]`)
          .forEach((input) => {
            input.disabled = true; // Disable all rating inputs after submission
          });
        document.getElementById("submitRatingsButton").disabled = true; // Disable the submit all button
      } else {
        alert("Error submitting ratings: " + data.message);
      }
    })
    .catch((error) => console.error("Error submitting ratings:", error));
}
