document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('#shipping-rules-table');
    const addRowButton = document.querySelector('.add-row');
    console.log(addRowButton);

    // Replace these URLs with your actual controller endpoints
    const saveUrl = '/path/to/controller/save';
    const deleteUrl = '/path/to/controller/delete';

    // Add a new row
    addRowButton.addEventListener('click', (e) => {
        e.preventDefault();
        const tp = table.querySelector('tr[data-id="-1"]');
        if( tp ) return;
        const countriesArray = Object.values(countries);
        const newRow = `
            <tr data-id="-1">
                <td>
                    <select class="form-control" name="products" multiple data-multi-select>
                        ${products.map(product => `<option value="${product.id_product}">${product.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select class="form-control" name="country">
                        ${countriesArray.map(country => `<option value="${country.id_country}">${country.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="number" class="form-control" name="start_fee" value="0"></td>
                <td><input type="number" class="form-control" name="extra_fee" value="0"></td>
                <td>
                    <button class="btn btn-success save-row">Save</button>
                    <button class="btn btn-danger delete-row">Cancel</button>
                </td>
            </tr>
        `;
        table.insertAdjacentHTML('afterBegin', newRow);
        // document.querySelectorAll('[data-multi-select]').forEach(select => new MultiSelect(select));
        const newSelect = table.querySelector('tbody tr:first-child select[data-multi-select]');
        new MultiSelect(newSelect);
    });

    // Save a row
    table.addEventListener('click', async (event) => {
        event.preventDefault();
        if (event.target.classList.contains('save-row')) {
            const row = event.target.closest('tr');
            console.log(row);
            const rowId = row.getAttribute('data-id');
            const products = Array.from(row.querySelectorAll('input[name="products[]"]')).map(input => input.value);
            const country = row.querySelector('select[name="country"]').value;
            const startFee = row.querySelector('input[name="start_fee"]').value;
            const extraFee = row.querySelector('input[name="extra_fee"]').value;
            const data = {
                id: rowId,
                id_country: country,
                shipping_start_rate: startFee,
                shipping_extra_rate: extraFee,
                products: products
            };
            console.log(data);
            const url = rowId === '-1' ? addShippingRuleUrl : updateShippingRuleUrl;
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                    const result = await response.json();
                if (result.status === 'success') {
                    location.reload();
                    // Optionally, update the UI or provide feedback to the user
                } else {
                    console.error('Failed to create shipping rule:', response.statusText);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });

    // Delete a row
    table.addEventListener('click', (event) => {
        event.preventDefault();
        if (event.target.classList.contains('delete-row')) {
            const row = event.target.closest('tr');
            const rowId = row.getAttribute('data-id');

            if (rowId === -1) {
                row.remove();
                return;
            }

            const data = new URLSearchParams();
            data.append('row_id', rowId);

            fetch(deleteShippingRuleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({id: rowId})
            })
                .then(response => response.json())
                .then(responseData => {
                    if (responseData.status === 'success') {
                        row.remove();
                    } else {
                        alert('Error deleting row: ' + responseData.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
});
