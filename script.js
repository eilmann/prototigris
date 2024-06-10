document.addEventListener('DOMContentLoaded', () => {
    const generateButton = document.getElementById('generate-button');
    const saveResultsButton = document.getElementById('save-results-button');

    generateButton.addEventListener('click', (event) => {
        event.preventDefault();
        const formID = document.getElementById('tournament-select').value;
        const eliminationType = document.querySelector('input[name="elimination-type"]:checked').value;
        fetchParticipants(formID, eliminationType);
    });

    saveResultsButton.addEventListener('click', (event) => {
        event.preventDefault();
        saveCurrentStage();
    });
});

function fetchParticipants(formID, eliminationType) {
    fetch(`fetch_participants.php?formID=${formID}`)
        .then(response => response.json())
        .then(participants => {
            if (participants.length === 0) {
                displayNoParticipantsMessage();
            } else {
                generateInitialRound(participants);
            }
        })
        .catch(error => console.error('Error fetching participants:', error));
}

function generateInitialRound(teams) {
    const container = document.getElementById('bracket-container');
    container.innerHTML = '';
    const bracket = document.createElement('div');
    bracket.className = 'bracket';

    const round = document.createElement('div');
    round.className = 'round';
    round.innerHTML = '<h3>Round 1</h3>';

    for (let i = 0; i < teams.length; i += 2) {
        const match = document.createElement('div');
        match.className = 'match';

        const team1 = document.createElement('div');
        team1.className = 'team';
        team1.textContent = teams[i];
        match.appendChild(team1);

        const team2 = document.createElement('div');
        team2.className = 'team';
        team2.textContent = teams[i + 1] ? teams[i + 1] : 'Bye';
        match.appendChild(team2);

        const selectWinner = document.createElement('select');
        selectWinner.className = 'winner-select';
        selectWinner.innerHTML = `
            <option value="">Select Winner</option>
            <option value="${teams[i]}">${teams[i]}</option>
            ${teams[i + 1] ? `<option value="${teams[i + 1]}">${teams[i + 1]}</option>` : ''}
        `;
        match.appendChild(selectWinner);

        round.appendChild(match);
    }

    bracket.appendChild(round);
    container.appendChild(bracket);
}

function displayNoParticipantsMessage() {
    const container = document.getElementById('bracket-container');
    const messageContainer = document.getElementById('message-container');
    container.innerHTML = '';
    messageContainer.innerHTML = '<p>No participants registered for this tournament.</p>';
}

function saveCurrentStage() {
    const winnerSelects = document.querySelectorAll('.winner-select');
    const winners = Array.from(winnerSelects).map(select => select.value).filter(value => value !== '');

    if (winners.length < winnerSelects.length) {
        alert('Please select a winner for each match.');
        return;
    }

    const formID = document.getElementById('tournament-select').value;

    fetch('save_stage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            formID: formID,
            winners: winners
        }),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
        alert(data.message);
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}
