/**
 * Perform a get request.
 *
 * @param url
 * @param data
 * @returns {Promise<any>}
 */
export async function get(url, data = {}) {
    const final_url = data && Object.keys(data).length === 0 ? url : url + '?' + (new URLSearchParams(data).toString());
    const response = await fetch(final_url);

    try {
        return await response.json();
    } catch(e) {
        return {
            error: "Error fetching data"
        };
    }
}
