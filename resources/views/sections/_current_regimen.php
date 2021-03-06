<div>
    <span class='reg_toggle' ng-model="show_fclties1" ng-init="show_reg=true" ng-click="showReg()">
        <span class='active' id='reg_shw'>&nbsp;&nbsp;REGIMEN GROUPS&nbsp;&nbsp;</span>
        <span id='dur_shw'>&nbsp;&nbsp;DURATION ON ART&nbsp;</span>
    </span>
    <br>
    <br>

    <div class="row" ng-show="show_reg">
        <div class="col-lg-6">                        
            <div id="regimen_groups" class="db-charts">
                <svg></svg>
            </div>                        
        </div>

        <div class="col-lg-6 facilties-sect " >
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th>Regimen&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.</th>
                        <th>Samples Received</th>
                        <th>Samples Tested</th>
                        <th>Valid Results</th>
                        <th>Number Suppressed</th>
                        <th>Suppression Rate</th>
                        <th>Percentage&nbsp;of Samples&nbsp;(%)</th>                        
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="regimen in regimen_numbers" >
                        <td class="ng-cloak"><% labels.regimens2[regimen._id] %></td>
                        <td class="ng-cloak"><% regimen.samples_received|number %></td>
                        <td class="ng-cloak"><% regimen.total_results|number %></td>
                        <td class="ng-cloak"><% regimen.valid_results|number %></td>
                        <td class="ng-cloak"><% regimen.suppressed|number %></td>
                        <td class="ng-cloak"><% ((regimen.suppressed/regimen.valid_results)*100)|number:1 %>%</td>
                        <td class="ng-cloak"><% ((regimen.samples_received/samples_received)*100 )| number:1 %> %</td>
                    </tr>                        
                 </tbody>
            </table>
          <br>
         <br>
         <button id="exportDistrictsRegimenNumbers" type="button" ng-csv="export_regimen_numbers"  class="btn btn-success" filename="regimen_groups_<%current_timestamp%>.csv" csv-header="['Regimen', 'Samples Received','Samples Tested','Valid Results','Number Suppressed','Suppression Rate','Percentage of Samples()']">Regimen Download CSV</button>

        </div>
       
    </div>

    <div class="row" ng-hide="show_reg">
        <div class="col-lg-6">                        
            <div id="regimen_time" class="db-charts">
                <svg></svg>
            </div>                        
        </div>

        <div class="col-lg-6 facilties-sect " >
            <table datatable="ng" class="row-border hover table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th>Time on Treatment</th>
                        <th>Samples Received</th>
                        <th>Percentage of Samples (%)</th>
                        <th>Samples Tested</th>
                        <th>Number Suppressed</th>                    
                    </tr>
                </thead>
                <tbody>                                
                    <tr ng-repeat="r in regimen_time_numbers | orderBy:'-samples_received'" >
                        <td id="time_on_treatment_column" class="ng-cloak"><% 
                            (labels.reg_times[r._id] == '6-12 months')? "0" + labels.reg_times[r._id] : 
                            (labels.reg_times[r._id] == '1-2 years')? "1" + labels.reg_times[r._id] :
                            (labels.reg_times[r._id] == '2-3 years')? "2" + labels.reg_times[r._id] :
                            (labels.reg_times[r._id] == '3-5 years')? "3" + labels.reg_times[r._id] :
                            (labels.reg_times[r._id] == '5+ years')? "4" + labels.reg_times[r._id] :
                            (labels.reg_times[r._id] == 'No Date Given')? "5" + labels.reg_times[r._id]: ''
                        %></td>
                        <td class="ng-cloak"><% r.samples_received|number %></td>
                        <td class="ng-cloak"><% ((r.samples_received/samples_received)*100 )| number:1 %> %</td>
                        <td class="ng-cloak"><% r.total_results|number %></td>
                        <td class="ng-cloak"><% r.suppressed|number %></td>                    
                    </tr>                        
                 </tbody>
            </table>
        </div>
     
        
         <br>
         <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <button id="exportDurationOnArt" type="button" ng-csv="export_duration_on_art" filename="duration_on_art_<%current_timestamp%>.csv" class="btn btn-success" csv-header="['Time on Treatment', 'Samples Received','Percentage of Samples (%)', 'Samples Tested','Number Suppressed']">Download CSV</button>
    </div>
    
    
        
</div>