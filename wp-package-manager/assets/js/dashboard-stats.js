// assets/js/dashboard-stats.js
jQuery(document).ready(function($) {
    if ( typeof PM_Dashboard_Data === 'undefined' ) {
        return;
    }

    // Sales Over Time Chart
    var salesCtx = document.getElementById('pm-sales-chart').getContext('2d');
    var salesLabels = [];
    var salesData   = [];
    $.each(PM_Dashboard_Data.sales_time, function(day, count) {
        salesLabels.push(day);
        salesData.push(count);
    });
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Πωλήσεις',
                data: salesData,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { display: true, title: { display: true, text: 'Ημερομηνία' } },
                y: { display: true, title: { display: true, text: 'Αριθμός Πωλήσεων' } }
            }
        }
    });

    // Package Popularity Chart
    var popCtx = document.getElementById('pm-popularity-chart').getContext('2d');
    var popLabels = PM_Dashboard_Data.popularity.map(function(item) { return item.label; });
    var popData   = PM_Dashboard_Data.popularity.map(function(item) { return item.count; });
    new Chart(popCtx, {
        type: 'bar',
        data: {
            labels: popLabels,
            datasets: [{
                label: 'Δημοφιλία Πακέτων',
                data: popData,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { display: true, title: { display: true, text: 'Πακέτο' } },
                y: { display: true, title: { display: true, text: 'Συνολικές Αριθμοί' } }
            }
        }
    });
});
