import * as request from './request.js';

export async function userRow(user) {
    const country_flag = await getCountryFlag(user.country);

    return (
        `<tr>` +
            `<td class="id"><span class="visible-mobile">ID: </span>${user.id}</td>` +
            `<td class="profile"><img src="${user.profile_pic}" alt="" /></td>` +
            `<td class="name">${user.name}</td>` +
            `<td class="email">${user.email}</td>` +
            `<td class="age"><span class="visible-mobile">Age: </span>${user.age}</td>` +
            `<td class="country"><span class="flag">${country_flag}</span> ${user.country}</td>` +
        `</tr>`
    )
}

function errorRow(error) {
    return (
        `<tr>` +
            `<td colspan="6" class="error">${error}</td>` +
        `</tr>`
    )
}

export async function appendUsers(users) {
    if ( users.length ) {
        resetTable();

        for (const user of users) {
            const row = await userRow(user);
            appendRow(row);
        }
    } else {
        appendRow(errorRow("No users yet!"));
    }
}

function appendRow(row_html) {
    document.querySelector('#users tbody').insertAdjacentHTML('beforeend', row_html);
}

function resetTable() {
    document.querySelector('#users tbody').innerHTML = '';
}

export function appendUsersFromResponse(response) {
    if ( response?.success ) {
        appendUsers(response.data);
    } else if ( response?.error) {
        appendRow(errorRow(response.error));
    }
}

async function getCountryFlag( country ) {
    let saved_country_code = JSON.parse(localStorage.getItem('countries'));

    if (!saved_country_code) {
        saved_country_code = {};
    }

    if (!saved_country_code[country]) {
        const data = await request.get(`https://restcountries.com/v3.1/name/${country}?fullText=true`);

        if ( data ) {
            saved_country_code[country] = data[0].flag;
            localStorage.setItem('countries', JSON.stringify(saved_country_code));
        }
    }

    return saved_country_code[country];
}