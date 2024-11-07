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

// Function to select/deselect a subject and fetch its competencies
function selectSubject(subjectCode, subjectName, buttonElement) {
  console.log(`Selecting subject: ${subjectCode} - ${subjectName}`);
  const isSelected = buttonElement.classList.contains("selected-subject");

  // Toggle the 'selected-subject' class
  if (isSelected) {
    console.log(`Deselecting subject: ${subjectCode}`);
    buttonElement.classList.remove("selected-subject");

    // Clear the selected subject from sessionStorage
    sessionStorage.removeItem("selectedSubjectCode");
    sessionStorage.removeItem("selectedSubjectName");

    // Clear the hidden input fields
    document.getElementById("selected_subject_code").value = "";
    document.getElementById("selected_subject_name").value = "";
    document.getElementById("syllabus_subject_code").value = "";
    document.getElementById("syllabus_subject_name").value = "";

    // Hide the filtered content (competencies, comments)
    filterContentBySubject("");
    clearCompetenciesTable();
    clearCommentsTable();
  } else {
    console.log(`Selecting new subject: ${subjectCode}`);
    // Deselect all other subject buttons first
    document.querySelectorAll(".btnSubjects button").forEach(function (button) {
      button.classList.remove("selected-subject");
    });

    // Select the clicked subject
    buttonElement.classList.add("selected-subject");

    // Store selected subject in sessionStorage
    sessionStorage.setItem("selectedSubjectCode", subjectCode);
    sessionStorage.setItem("selectedSubjectName", subjectName);

    // Update hidden input fields in the Competencies and Syllabus form
    document.getElementById("selected_subject_code").value = subjectCode;
    document.getElementById("selected_subject_name").value = subjectName;
    document.getElementById("syllabus_subject_code").value = subjectCode;
    document.getElementById("syllabus_subject_name").value = subjectName;

    // Filter content by selected subject
    filterContentBySubject(subjectCode);

    // Fetch and display competencies for the selected subject
    fetchCompetencies(subjectCode);

    // Fetch and display comments for the selected subject
    fetchTopicsAndComments(subjectCode);
  }
}

// Function to filter content by selected subject
function filterContentBySubject(subjectCode) {
  console.log(`Filtering content for subject: ${subjectCode}`);

  // Update Plan Cards
  document.querySelectorAll(".planCard").forEach(function (card) {
    if (
      card.getAttribute("data-subject-code") === subjectCode ||
      card.getAttribute("data-subject-code") === ""
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });

  // Update Competency Items
  document.querySelectorAll(".remarksTable").forEach(function (table) {
    if (
      table.getAttribute("data-subject-code") === subjectCode ||
      table.getAttribute("data-subject-code") === ""
    ) {
      table.style.display = "table";
    } else {
      table.style.display = "none";
    }
  });

  // Update Comments
  document.querySelectorAll(".commentCard").forEach(function (card) {
    if (
      card.closest("#containerComment").getAttribute("data-subject-code") ===
        subjectCode ||
      card.closest("#containerComment").getAttribute("data-subject-code") === ""
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

// Function to fetch competencies for the selected subject
function fetchCompetencies(subjectCode) {
  console.log(`Fetching competencies for subject: ${subjectCode}`);
  const request = new XMLHttpRequest();
  request.open("POST", "fetch_competencies.php", true);
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  request.onload = function () {
    if (this.status === 200) {
      const response = JSON.parse(this.responseText);
      const tableBody = document.querySelector("#competenciesTable tbody");
      const noCompetenciesRow = document.getElementById("noCompetencies");

      // Clear existing rows
      tableBody.querySelectorAll("tr").forEach((row) => row.remove());

      if (response.length > 0) {
        response.forEach(function (competency) {
          const row = document.createElement("tr");

          row.innerHTML = `  
            <td style="padding: 10px 15px; text-align: left;">${competency.competency_description}</td>
            <td style="padding: 10px 15px; text-align: center;">
                <label>
                    <input type="checkbox" disabled ${
                      competency.remarks === "IMPLEMENTED" ? "checked" : ""
                    } style="transform: scale(2.3); margin-right: 10px; overflow-y: auto;" />
                </label>
            </td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        const noCompetenciesRow = document.createElement("tr");
        noCompetenciesRow.innerHTML = `<td colspan="2">No competencies found for this subject.</td>`;
        tableBody.appendChild(noCompetenciesRow);
      }
    } else {
      console.log(`Failed to fetch competencies for ${subjectCode}`);
    }
  };
  request.send(`subject_code=${encodeURIComponent(subjectCode)}`);
}

// Function to clear the competencies table and display the original structure
function clearCompetenciesTable() {
  const container = document.querySelector("#container");

  // Clear the existing content inside the container
  container.innerHTML = "";

  // Create the table structure
  const tableHTML = `
    <table class="remarksTable" id="competenciesTable" data-subject-code="">
        <thead>
            <tr>
                <th>Competencies</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr id="noCompetencies">
                <td colspan="2" style="text-align: center; font-style: italic; color: #888;">
                    No competencies found for this subject.
                </td>
            </tr>
        </tbody>
    </table>
  `;

  // Insert the table HTML into the container
  container.innerHTML = tableHTML;
}

// Function to fetch Topics and Comments for the selected subject via AJAX
function fetchTopicsAndComments(subjectCode) {
  console.log(`Fetching topics and comments for subject: ${subjectCode}`);
  const formData = new FormData();
  formData.append("subject_code", subjectCode);

  fetch("fetch_comments.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      appendCommentsToContainer(data);
    })
    .catch((error) => {
      console.error("Error fetching topics and comments:", error);
      document.querySelector("#containerComment").innerHTML =
        "<p>Error fetching comments. Please try again later.</p>";
    });
}

// Function to clear the comments table
function clearCommentsTable() {
  const commentContainer = document.querySelector("#containerComment");
  commentContainer.innerHTML = "<p>No comments available.</p>";
}

// Function to dynamically append the comments to the comments container
function appendCommentsToContainer(data) {
  const commentContainer = document.querySelector("#containerComment");

  // Clear previous content
  commentContainer.innerHTML = "";

  if (data && Object.keys(data).length > 0) {
    let commentCounter = 1; // Initialize a counter for comments

    // Iterate over ILOs
    Object.keys(data).forEach((iloKey) => {
      const iloData = data[iloKey];

      if (iloData.comments && iloData.comments.length > 0) {
        iloData.comments.forEach((comment) => {
          const commentHTML = `
            <div class="commentCard">
              <p><strong>Comment #${commentCounter}</strong><br> <strong>ILO:</strong> ${iloData.ilo || "N/A"}</p>
              <p><strong>Comment: </strong>${comment.comment || "No comment provided"}</p>
              <p><strong>Date: </strong> ${new Date(comment.timestamp)
                .toLocaleString("en-US", { hour: "numeric", minute: "numeric", hour12: true, year: "numeric", month: "long", day: "numeric" })
                .replace(" at ", " || ")}</p>
            </div>
          `;
          // Append each comment for the ILO
          commentContainer.innerHTML += commentHTML;
          commentCounter++; // Increment the counter for each comment
        });
      } else {
        commentContainer.innerHTML += `<p>No comments for ILO: ${iloData.ilo || "N/A"}</p>`;
      }
    });
  } else {
    commentContainer.innerHTML = "<p>No comments available for the selected subject.</p>";
  }
}

// Initialize the page and auto-select the previously selected subject
document.addEventListener("DOMContentLoaded", function () {
  // Check if a subject was selected before
  var selectedSubjectCode = sessionStorage.getItem("selectedSubjectCode");
  var selectedSubjectName = sessionStorage.getItem("selectedSubjectName");

  if (selectedSubjectCode && selectedSubjectName) {
    // Auto-select the subject if previously selected
    var subjectButton = document.querySelector(
      `.btnSubjects button[data-subject-code="${selectedSubjectCode}"]`
    );
    if (subjectButton) {
      selectSubject(selectedSubjectCode, selectedSubjectName, subjectButton);
    }
  }
});

// Automatically fetch comments for the default or selected subject
document.querySelectorAll(".btnSubjects button").forEach((button) => {
  button.addEventListener("click", function () {
    const subjectCode = this.getAttribute("data-subject-code");
    selectSubject(subjectCode, this.getAttribute("data-subject-name"), this); // Handle subject selection/deselection
  });
});
