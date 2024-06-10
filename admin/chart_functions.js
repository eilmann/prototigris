// Function to fetch chart data from server
async function fetchChartData() {
    const response = await fetch('get_chart_data.php');
    const data = await response.json();
    return data;
}

// Function to create Total Tournaments Chart by Month
function createTotalTournamentsChart(tournaments) {
    const labels = tournaments.map(t => t.month);
    const data = tournaments.map(t => t.totalTournaments);
    const ctx = document.getElementById('totalTournamentsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '# of Tournaments',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to create Participants Per Tournament Chart
function createParticipantsPerTournamentChart(participants) {
    const labels = participants.map(p => p.tournamentName);
    const data = participants.map(p => p.participantCount);
    const ctx = document.getElementById('participantsPerTournamentChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '# of Participants',
                data: data,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to create Match Schedules Chart
function createMatchSchedulesChart(matches) {
    const labels = matches.map(m => m.match_date);
    const data = matches.map(m => m.matchCount);
    const ctx = document.getElementById('matchSchedulesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '# of Matches',
                data: data,
                fill: false,
                borderColor: 'rgba(255, 159, 64, 1)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to create Top Participants Chart
function createTopParticipantsChart(participants) {
    const labels = participants.map(p => p.participantName);
    const firstPlaceData = participants.map(p => p.firstPlaceCount);
    const secondPlaceData = participants.map(p => p.secondPlaceCount);
    const thirdPlaceData = participants.map(p => p.thirdPlaceCount);

    const ctx = document.getElementById('topParticipantsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'First Place',
                    data: firstPlaceData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Second Place',
                    data: secondPlaceData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Third Place',
                    data: thirdPlaceData,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to create Tournaments By Game Title Chart
function createTournamentsByGameTitleChart(tournaments) {
    const labels = tournaments.map(t => t.gameTitle);
    const data = tournaments.map(t => t.tournamentCount);
    const ctx = document.getElementById('tournamentsByGameTitleChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: '# of Tournaments',
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

// Function to create Most Active Participants Chart
function createMostActiveParticipantsChart(participants) {
    const labels = participants.map(p => p.participantName);
    const data = participants.map(p => p.totalParticipant);
    const ctx = document.getElementById('mostActiveParticipantsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '# of Participations',
                data: data,
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function to create Top Participants by Game Title
function createTopParticipantsByGameTitle(participants) {
    const container = document.getElementById('topParticipantsByGameContainer');
    container.innerHTML = ''; // Clear any previous content

    participants.forEach(participant => {
        const div = document.createElement('div');
        div.className = 'participant-card';

        const img = document.createElement('img');
        img.src = participant.participantPic;
        img.alt = participant.participantName;
        img.className = 'participant-img';

        const name = document.createElement('p');
        name.innerText = participant.participantName;
        name.className = 'participant-name';

        const gameTitle = document.createElement('p');
        gameTitle.innerText = participant.gameTitle;
        gameTitle.className = 'game-title';

        div.appendChild(img);
        div.appendChild(name);
        div.appendChild(gameTitle);
        container.appendChild(div);
    });
}

// Function to generate random color
function getRandomColor() {
    const r = Math.floor(Math.random() * 255);
    const g = Math.floor(Math.random() * 255);
    const b = Math.floor(Math.random() * 255);
    return `rgba(${r}, ${g}, ${b}, 0.2)`;
}

// Initialize Charts
async function initializeCharts() {
    const data = await fetchChartData();

    createTotalTournamentsChart(data.totalTournaments);
    createParticipantsPerTournamentChart(data.participantsPerTournament);
    createMatchSchedulesChart(data.matchSchedules);
    createTopParticipantsChart(data.topParticipants);
    createTournamentsByGameTitleChart(data.tournamentsByGameTitle);
    createMostActiveParticipantsChart(data.mostActiveParticipants);
    createTopParticipantsByGameTitle(data.topParticipantsByGame);
}

// Call the function to initialize charts
initializeCharts();
