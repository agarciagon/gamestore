function format(items) {
    if (!items || items.length === 0) return '<div class="p-3 text-gray-400">No details available</div>';

    let html = '<table class="w-full text-xs bg-mauve-600 rounded-lg overflow-hidden justify-evenly">';

    html += `
        <thead class="bg-mauve-300 text-gray-500">
            <tr>
                <th class="px-4 py-2">Game</th>
                <th class="px-4 py-2">Qty</th>
                <th class="px-4 py-2">Unit Price</th>
                <th class="px-4 py-2">Subtotal</th>
            </tr>
        </thead>
    `;

    html += '<tbody>';
items.forEach((item, index) => {
    const subtotal = parseFloat(item.precio_unidad) * item.cantidad;
    const border = index > 0 ? 'style="border-top: 1px solid #e0e0e0;"' : '';
    html += `
        <tr ${border}>
            <td class="px-4 py-2 text-white">${item.titulo}</td>
            <td class="px-4 py-2 text-gray-300">${item.cantidad}</td>
            <td class="px-4 py-2 text-gray-300">${parseFloat(item.precio_unidad).toFixed(2)}€</td>
            <td class="px-4 py-2 text-white font-semibold">${subtotal.toFixed(2)}€</td>
        </tr>
    `;
});
html += '</tbody>';

    html += '</table>';
    return html;
}

$(document).ready(function () {

    var table = $('#ordersTable').DataTable({
        pageLength: 5,
        columnDefs: [{ orderable: false, targets: 0 }]
    });

    $('#ordersTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        // Obtiene el nodo DOM correcto según DataTables
        var trNode = $(table.row(tr).node());
        var items = JSON.parse(trNode.attr('data-items') || '[]');

        if (row.child.isShown()) {
            row.child.hide();
            $(this).text('+');
        } else {
            row.child(format(items)).show();
            $(this).text('-');
        }
    });

});

function switchTab(tabName) {

    document.getElementById('tab-content-orders').classList.add('hidden');
    document.getElementById('tab-content-profile').classList.add('hidden');

    document.getElementById('tab-content-' + tabName).classList.remove('hidden');

    if (tabName === 'orders') {
        setTimeout(function () {
            $.fn.dataTable
                .tables({ visible: true, api: true })
                .columns.adjust();
        }, 100);
    }
}