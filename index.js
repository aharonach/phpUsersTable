import * as request from './public/js/request.js';
import * as dom from './public/js/dom.js';

const URL      = 'api.php';
const response = await request.get(URL, {
    action: 'get',
    results: 10
});

// Append initial users from API.
dom.appendUsersFromResponse(response);

/**
 * Handle click for insert button
 */
document.querySelectorAll('#insert').forEach( el => el.addEventListener('click', async () => {
    const response = await request.get(URL, {
        action: 'insert'
    });

    dom.appendUsersFromResponse(response);
}));

/**
 * Handle filter by name
 */
document.querySelector('#filter').addEventListener('keyup', event => {
    const value = event.target.value.toLowerCase();

    document.querySelectorAll("#users tbody tr").forEach( el => {
        el.querySelector('.name').innerText.toLowerCase().includes(value)
            ? el.classList.remove('hide')
            : el.classList.add('hide');
    });
});