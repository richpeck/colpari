<script type="text/template" id="ea-appointments-overview">
    <small><%= settings['trans.overview-message'] %></small>
    <table>
        <tbody>
        <% if(settings['rtl'] == '1') { %>
            <% if(data.location.indexOf('_') !== 0) { %>
            <tr class="row-location">
                <td class="ea-label"><%= _.escape( settings['trans.location'] ) %></td>
                <td class="value"><%= _.escape( data.location ) %></td>
            </tr>
            <% } %>
            <% if(data.service.indexOf('_') !== 0) { %>
            <tr class="row-service">
                <td class="ea-label"><%= _.escape( settings['trans.service'] ) %></td>
                <td class="value"><%= _.escape( data.service ) %></td>
            </tr>
            <% } %>
            <% if(data.worker.indexOf('_') !== 0) { %>
            <tr class="row-worker">
                <td class="ea-label"><%= _.escape( settings['trans.worker'] ) %></td>
                <td class="value"><%= _.escape( data.worker ) %></td>
            </tr>
            <% } %>
            <% if (settings['price.hide'] !== '1') { %>
            <tr class="row-price">
                <td class="ea-label"><%= settings['trans.price'] %></td>
                <td class="value"><%= _.escape( data.price ) %> <%= _.escape( settings['trans.currency'] ) %></td>
            </tr>
            <% } %>
            <tr class="row-datetime">
                <td class="ea-label"><%= settings['trans.date-time'] %></td>
                <td class="value"><%= data.date %> <%= data.time %></td>
            </tr>
        <% } else { %>
            <% if(data.location.indexOf('_') !== 0) { %>
            <tr class="row-location">
                <td class="ea-label"><%= _.escape( settings['trans.location'] ) %></td>
                <td class="value"><%= _.escape( data.location ) %></td>
            </tr>
            <% } %>
            <% if(data.service.indexOf('_') !== 0) { %>
            <tr class="row-service">
                <td class="ea-label"><%= _.escape( settings['trans.service'] ) %></td>
                <td class="value"><%= _.escape( data.service ) %></td>
            </tr>
            <% } %>
            <% if(data.worker.indexOf('_') !== 0) { %>
            <tr class="row-worker">
                <td class="ea-label"><%= _.escape( settings['trans.worker'] ) %></td>
                <td class="value"><%= _.escape( data.worker ) %></td>
            </tr>
            <% } %>
            <% if (settings['price.hide'] !== '1') { %>
            <tr class="row-price">
                <td class="ea-label"><%= _.escape( settings['trans.price'] ) %></td>
                <% if (settings['currency.before'] == '1') { %>
                <td class="value"><%= settings['trans.currency'] %><%= _.escape( data.price ) %></td>
                <% } else { %>
                <td class="value"><%= _.escape( data.price ) %><%= _.escape( settings['trans.currency'] ) %></td>
                <% } %>
            </tr>
            <% } %>
            <tr class="row-datetime">
                <td class="ea-label"><%= settings['trans.date-time'] %></td>
                <td class="value"><%= data.date_time %></td>
            </tr>
        <% } %>
        </tbody>
    </table>
    <div id="ea-total-amount" style="display: none;" data-total="<%= data.price %>"></div>
</script>
