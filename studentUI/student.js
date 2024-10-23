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
                           placeholder="${
                             commentExists
                               ? "Comments already Submitted"
                               : "Enter comments..."
                           }" 
                           class="ilo-comment" 
                           style="width: 200px;" 
                           ${
                             commentExists ? "disabled" : ""
                           }><!-- Disable input if comment exists -->
                    <button style="${
                      commentExists ? "pointer-events: none; opacity: 0.5;" : ""
                    }" 
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
  //console.log("Checking comment existence for:", {
  //subject_code: subjectCode,
  //ilo: ilo,
  // }); // Debug log

  const response = await fetch("check_comments.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ subject_code: subjectCode, ilo: ilo }),
  });

  const data = await response.json();
  //console.log("Response from check_comments.php:", data); // Debug log
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

async function checkRatingExists(subjectCode, topic) {
  const response = await fetch("check_rating.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ subject_code: subjectCode }),
  });

  const data = await response.json();
  //console.log(
  //  `Rating existence response for subject: ${subjectCode}, topic: ${topic}`,
  //  data
 // );

  return data.success && data.ratings && topic in data.ratings;
}

async function fetchExistingRatings(subjectCode, topics) {
  const ratings = {};

 // console.log(
//    `Fetching existing ratings for subject: ${subjectCode} and topics: ${topics}`
//  );

  for (const topic of topics) {
    const exists = await checkRatingExists(subjectCode, topic);
   // console.log(`Rating existence for topic '${topic}': ${exists}`);

    if (exists) {
      const ratingResponse = await fetch("check_rating.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ subject_code: subjectCode }),
      });

      const ratingData = await ratingResponse.json();
    //  console.log(`Fetched rating data for topic '${topic}':`, ratingData);
      ratings[topic.trim()] = ratingData.ratings[topic.trim()] || 0;
    } else {
      ratings[topic.trim()] = 0;
    }
  }

// console.log("Existing ratings collected:", ratings);
  return ratings;
}

async function appendTopicsToTable(data, subjectCode) {
  const tableBody = document.querySelector("#Topics .remarksTable tbody");
  const submitButton = document.querySelector("#submitRatingsButton");

  tableBody.innerHTML = ""; // Clear existing rows

  // Check for no data message
  if (data.message) {
    const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
    tableBody.innerHTML = noDataRow;
    // Disable the submit button
    if (submitButton) {
      submitButton.disabled = true; // Disable the submit button
      submitButton.classList.add("disabled"); // Add disabled class
    }
    return; // Exit the function
  }

  let hasData = false;
  const topics = [];

  for (const section of ["PRELIM", "MIDTERM", "SEMIFINAL", "FINAL"]) {
    if (data[section] && data[section].length > 0) {
      hasData = true;
      topics.push(...data[section]);
    }
  }

  const existingRatings = await fetchExistingRatings(subjectCode, topics);
//  console.log("Existing ratings after fetching:", existingRatings);

  // Render topic rows
  for (const section of ["PRELIM", "MIDTERM", "SEMIFINAL", "FINAL"]) {
    if (data[section] && data[section].length > 0) {
      for (const topic of data[section]) {
        const topicKey = `${topic}_${section}`;
        const rating = existingRatings[topic.trim()] || 0;

        const shouldDisable = rating > 0;

        const row = `
          <tr data-topic="${topicKey}">
            <td>${topic} (${section})</td>
            <td>
              <input type="radio" name="${topicKey}_rating" value="1" ${
          shouldDisable ? "disabled" : ""
        } ${rating == 1 ? "checked" : ""}>1
              <input type="radio" name="${topicKey}_rating" value="2" ${
          shouldDisable ? "disabled" : ""
        } ${rating == 2 ? "checked" : ""}>2
              <input type="radio" name="${topicKey}_rating" value="3" ${
          shouldDisable ? "disabled" : ""
        } ${rating == 3 ? "checked" : ""}>3
              <input type="radio" name="${topicKey}_rating" value="4" ${
          shouldDisable ? "disabled" : ""
        } ${rating == 4 ? "checked" : ""}>4
              <input type="radio" name="${topicKey}_rating" value="5" ${
          shouldDisable ? "disabled" : ""
        } ${rating == 5 ? "checked" : ""}>5
            </td>
          </tr>`;
        tableBody.innerHTML += row;
      }
    }
  }

  // Manage submit button state
  const allRadioButtons = tableBody.querySelectorAll('input[type="radio"]');
  const allDisabled = Array.from(allRadioButtons).every(
    (input) => input.disabled
  );
  const hasZeroRatings = Array.from(allRadioButtons).some(
    (input) => !input.checked && !input.disabled
  ); // Check if any radio button is zero

  if (!hasData) {
    const noDataRow = `<tr><td colspan="2" class="no-data-message">No topics available for the selected subject.</td></tr>`;
    tableBody.innerHTML = noDataRow;
    if (submitButton) {
      submitButton.disabled = true; // Disable the submit button
      submitButton.classList.add("disabled"); // Add disabled class
    }
  } else {
    if (submitButton) {
      // Disable submit button if all radio buttons are disabled or if there's no topic with a rating of zero
      const shouldDisableButton = allDisabled || !hasZeroRatings;
      submitButton.disabled = shouldDisableButton;

      if (shouldDisableButton) {
        submitButton.classList.add("disabled"); // Add disabled class
        submitButton.setAttribute("aria-disabled", "true"); // Set aria attribute for accessibility
      } else {
        submitButton.classList.remove("disabled"); // Remove disabled class
        submitButton.removeAttribute("aria-disabled"); // Remove aria attribute
      }
    } else {
      // Append the submit button if it wasn't already added

      const newSubmitButtonRow = `
  <tr>
    <td colspan="2">
      <button id="submitRatingsButton" 
              ${!hasData || allDisabled || !hasZeroRatings ? "disabled" : ""} 
              onclick="submitAllRatings('${subjectCode}')" 
              class="${
                !hasData || allDisabled || !hasZeroRatings ? "disabled" : ""
              }">
        Submit All Ratings
      </button>
    </td>
  </tr>`;
      tableBody.innerHTML += newSubmitButtonRow;
    }
  }

  // Add CSS for the disabled button
  if (!document.getElementById("disabled-button-styles")) {
    const style = document.createElement("style");
    style.id = "disabled-button-styles";
    style.textContent = `
      .disabled {
        background-color: gray; /* Change this color as desired */
        color: white; /* Change text color if needed */
        opacity: 0.6; /* Optional: make the button appear faded */
      }
    `;
    document.head.appendChild(style);
  }
}

// Function to submit all ratings for the topics in a single submission
function submitAllRatings(subjectCode) {
  const ratings = {};
  const topicRows = document.querySelectorAll(".remarksTable tbody tr");

  topicRows.forEach((row) => {
    const topic = row.getAttribute("data-topic");
    const selectedRating = row.querySelector(
      `input[name="${topic}_rating"]:checked`
    );

    if (selectedRating) {
      ratings[topic] = selectedRating.value;
    }
  });

  if (Object.keys(ratings).length === 0) {
    alert("Please select at least one rating before submitting.");
    return;
  }

  const formData = new FormData();
  formData.append("subject_code", subjectCode);
  formData.append("ratings", JSON.stringify(ratings));

  fetch("submit_rating.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(`${data.message}`);
        document
          .querySelectorAll('input[type="radio"]:checked')
          .forEach((input) => {
            input.checked = false;
          });
        // Disable all radio buttons
        document
          .querySelectorAll('.remarksTable input[type="radio"]')
          .forEach((input) => {
            input.disabled = true;
          });
        // Disable the submit button
        const submitButton = document.querySelector("#submitRatingsButton");
        submitButton.disabled = true;
      } else {
        alert("Failed to submit some or all ratings.");
      }
    })
    .catch((error) => console.error("Error submitting ratings:", error));
}
