/**
 * Created by Robert on 11/22/2014.
 */
//<![CDATA[
$(function() {
    'use strict';
    if ($("#list").length) {
        var project = $("#projectId").val();
        var expedition = $("#expeditionId").val();
        $.ajax({
            type: "GET",
            url: "/projects/" + project + "/expeditions/" + expedition + "/grids/load",
            dataType: "json",
            success: jqGrid(project, expedition)
        });
    }
});

/*global $ */
$.jgrid.formatter.integer.thousandsSeparator = ',';
$.jgrid.formatter.number.thousandsSeparator = ',';
$.jgrid.formatter.currency.thousandsSeparator = ',';
function jqGrid(project, expedition)
{
    return function(result) {
        var cm = result.colModel;
        for (var i = 0; i < cm.length; i++) {
            var col = cm[i];
            if (col.hasOwnProperty("formatter") &&
                functionsMapping.hasOwnProperty(col.formatter)) {
                // fix colM[i].formatter from string to the function
                col.formatter = functionsMapping[col.formatter];
            }
        };
        var $grid = $("#list"),
        initDateSearch = function (elem) {
            setTimeout(function () {
                $(elem).datepicker({
                    dateFormat: 'dd-M-yy',
                    autoSize: true,
                    //showOn: 'button', // it dosn't work in searching dialog
                    changeYear: true,
                    changeMonth: true,
                    showButtonPanel: true,
                    showWeek: true,
                    onSelect: function () {
                        if (this.id.substr(0, 3) === "gs_") {
                            setTimeout(function () {
                                $grid[0].triggerToolbar();
                            }, 50);
                        } else {
                            // to refresh the filter
                            $(this).trigger('change');
                        }
                    }
                });
            }, 100);
        },
        numberSearchOptions = ['eq', 'ne', 'lt', 'le', 'gt', 'ge', 'nu', 'nn', 'in', 'ni'],
        numberTemplate = {
            formatter: 'number', align: 'right', sorttype: 'number',
            searchoptions: {sopt: numberSearchOptions}
        },
        myDefaultSearch = 'cn',
        getColumnIndex = function (grid, columnIndex) {
            var cm = grid.jqGrid('getGridParam', 'colModel'), i, l = cm.length;
            for (i = 0; i < l; i++) {
                if ((cm[i].index || cm[i].name) === columnIndex) {
                    return i; // return the colModel index
                }
            }
            return -1;
        },
        refreshSerchingToolbar = function ($grid, myDefaultSearch) {
            var postData = $grid.jqGrid('getGridParam', 'postData'), filters, i, l,
                rules, rule, iCol, cm = $grid.jqGrid('getGridParam', 'colModel'),
                cmi, control, tagName;

            for (i = 0, l = cm.length; i < l; i++) {
                control = $("#gs_" + $.jgrid.jqID(cm[i].name));
                if (control.length > 0) {
                    tagName = control[0].tagName.toUpperCase();
                    if (tagName === "SELECT") { // && cmi.stype === "select"
                        control.find("option[value='']")
                            .attr('selected', 'selected');
                    } else if (tagName === "INPUT") {
                        control.val('');
                    }
                }
            }

            if (typeof (postData.filters) === "string" &&
                typeof ($grid[0].ftoolbar) === "boolean" && $grid[0].ftoolbar) {
                filters = $.parseJSON(postData.filters);
                if (filters && filters.groupOp === "AND" && typeof (filters.groups) === "undefined") {
                    // only in case of advance searching without grouping we import filters in the
                    // searching toolbar
                    rules = filters.rules;
                    for (i = 0, l = rules.length; i < l; i++) {
                        rule = rules[i];
                        iCol = getColumnIndex($grid, rule.field);
                        if (iCol >= 0) {
                            cmi = cm[iCol];
                            control = $("#gs_" + $.jgrid.jqID(cmi.name));
                            if (control.length > 0 &&
                                (((typeof (cmi.searchoptions) === "undefined" ||
                                typeof (cmi.searchoptions.sopt) === "undefined")
                                && rule.op === myDefaultSearch) ||
                                (typeof (cmi.searchoptions) === "object" &&
                                $.isArray(cmi.searchoptions.sopt) &&
                                cmi.searchoptions.sopt.length > 0 &&
                                cmi.searchoptions.sopt[0] === rule.op))) {
                                tagName = control[0].tagName.toUpperCase();
                                if (tagName === "SELECT") { // && cmi.stype === "select"
                                    control.find("option[value='" + $.jgrid.jqID(rule.data) + "']")
                                        .attr('selected', 'selected');
                                } else if (tagName === "INPUT") {
                                    control.val(rule.data);
                                }
                            }
                        }
                    }
                }
            }
        },
        //cm = result.colModel,
        saveObjectInLocalStorage = function (storageItemName, object) {
            if (typeof window.localStorage !== 'undefined') {
                window.localStorage.setItem(storageItemName, JSON.stringify(object));
            }
        },
        removeObjectFromLocalStorage = function (storageItemName) {
            if (typeof window.localStorage !== 'undefined') {
                window.localStorage.removeItem(storageItemName);
            }
        },
        getObjectFromLocalStorage = function (storageItemName) {
            if (typeof window.localStorage !== 'undefined') {
                return JSON.parse(window.localStorage.getItem(storageItemName));
            }
        },
        myColumnStateName = function (grid) {
            return window.location.pathname + '#' + grid[0].id;
        },
        idsOfSelectedRows = [],
        saveColumnState = function (perm) {
            var colModel = this.jqGrid('getGridParam', 'colModel'), i, l = colModel.length, colItem, cmName,
                postData = this.jqGrid('getGridParam', 'postData'),
                columnsState = {
                    search: this.jqGrid('getGridParam', 'search'),
                    page: this.jqGrid('getGridParam', 'page'),
                    rowNum: this.jqGrid('getGridParam', 'rowNum'),
                    sortname: this.jqGrid('getGridParam', 'sortname'),
                    sortorder: this.jqGrid('getGridParam', 'sortorder'),
                    permutation: perm,
                    selectedRows: idsOfSelectedRows,
                    colStates: {}
                },
                colStates = columnsState.colStates;

            if (typeof (postData.filters) !== 'undefined') {
                columnsState.filters = postData.filters;
            }

            for (i = 0; i < l; i++) {
                colItem = colModel[i];
                cmName = colItem.name;
                if (cmName !== 'rn' && cmName !== 'cb' && cmName !== 'subgrid') {
                    colStates[cmName] = {
                        width: colItem.width,
                        hidden: colItem.hidden
                    };
                }
            }
            saveObjectInLocalStorage(myColumnStateName(this), columnsState);
        },
        myColumnsState,
        isColState,
        restoreColumnState = function (colModel) {
            var colItem, i, l = colModel.length, colStates, cmName,
                columnsState = getObjectFromLocalStorage(myColumnStateName(this));

            if (columnsState) {
                colStates = columnsState.colStates;
                for (i = 0; i < l; i++) {
                    colItem = colModel[i];
                    cmName = colItem.name;
                    if (cmName !== 'rn' && cmName !== 'cb' && cmName !== 'subgrid') {
                        colModel[i] = $.extend(true, {}, colModel[i], colStates[cmName]);
                    }
                }
            }
            return columnsState;
        },
        updateIdsOfSelectedRows = function (id, isSelected) {
            var index = $.inArray(id, idsOfSelectedRows);
            if (!isSelected && index >= 0) {
                idsOfSelectedRows.splice(index, 1); // remove id from the list
            } else if (index < 0) {
                idsOfSelectedRows.push(id);
            }
        },
        updateUrlLinks = function() {
            $('td.thumbPreview a').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            $.ajax({
                                url: api.elements.target.attr('title') // Use href attribute as URL
                            })
                                .then(function(content) {
                                    // Set the tooltip content upon successful retrieval
                                    api.set('content.text', content);
                                }, function(xhr, status, error) {
                                    // Upon failure... set the tooltip content to error
                                    api.set('content.text', status + ': ' + error);
                                });

                            return 'Loading...'; // Set some initial text
                        }
                    },
                    position: {
                        viewport: $(window)
                    },
                    style: 'qtip-wiki'
                });
            });
        },
        firstLoad = true;1
        myColumnsState = restoreColumnState.call($grid, cm);
        isColState = typeof (myColumnsState) !== 'undefined' && myColumnsState !== null;
        idsOfSelectedRows = isColState && typeof (myColumnsState.selectedRows) !== "undefined" ? myColumnsState.selectedRows : [];

        $grid.jqGrid({
            jsonReader: {
                repeatitems: false,
                root: "rows",
                page: "page",
                total: "total",
                records: "records",
                cell: "",
                id: "_id"
            },
            url: "/projects/" + project + "/expeditions/" + expedition + "/grids",
            datatype: 'json',
            mtype: 'GET',
            colNames: result.colNames,
            colModel: cm,
            rowNum: isColState ? myColumnsState.rowNum : 10,
            rowList: [10, 20, 50, 100],
            pager: '#pager',
            gridview: true,
            page: isColState ? myColumnsState.page : 1,
            search: isColState ? myColumnsState.search : false,
            postData: isColState ? {filters: myColumnsState.filters} : {},
            sortname: isColState ? myColumnsState.sortname : 'id',
            sortorder: isColState ? myColumnsState.sortorder : 'desc',
            rownumbers: true,
            ignoreCase: true,
            multiselect: true,
            shrinkToFit: false,
            autowidth: true,
            viewrecords: true,
            autoencode: true,
            caption: 'Subjects',
            height: '100%',
            onSelectRow: function (id, isSelected) {
                updateIdsOfSelectedRows(id, isSelected);
                saveColumnState.call($grid, $grid[0].p.remapColumns);
            },
            onSelectAll: function (aRowids, isSelected) {
                var i, count, id;
                for (i = 0, count = aRowids.length; i < count; i++) {
                    id = aRowids[i];
                    updateIdsOfSelectedRows(id, isSelected);
                }
                saveColumnState.call($grid, $grid[0].p.remapColumns);
            },
            loadComplete: function () {
                var $this = $(this), i, count;

                if (firstLoad) {
                    firstLoad = false;
                    if (isColState) {
                        $this.jqGrid("remapColumns", myColumnsState.permutation, true);
                    }
                    if (typeof (this.ftoolbar) !== "boolean" || !this.ftoolbar) {
                        // create toolbar if needed
                        $this.jqGrid('filterToolbar',
                            {stringResult: true, searchOnEnter: true, defaultSearch: myDefaultSearch});
                    }
                }
                var data = $this.getDataIDs();
                for(var x = 0; x < data.length; x++){
                    var row = $this.jqGrid ('getRowData', data[x]);
                    if (row.expedition_ids == "Yes") {
                        $this.setSelection(data[x], false);
                    }
                }
                refreshSerchingToolbar($this, myDefaultSearch);
                for (i = 0, count = idsOfSelectedRows.length; i < count; i++) {
                    $this.jqGrid('setSelection', idsOfSelectedRows[i], false);
                }
                saveColumnState.call($this, this.p.remapColumns);
            },
            resizeStop: function () {
                saveColumnState.call($grid, $grid[0].p.remapColumns);
            },
            gridComplete: function() {
                updateUrlLinks.call();
            }
        });
        $.extend($.jgrid.search, {
            multipleSearch: true,
            multipleGroup: true,
            recreateFilter: true,
            closeOnEscape: true,
            closeAfterSearch: true,
            overlay: 0,
            odata: [{ oper:'eq', text:'equal'},{ oper:'ne', text:'not equal'},{ oper:'lt', text:'less'},{ oper:'le', text:'less or equal'},{ oper:'gt', text:'greater'},{ oper:'ge', text:'greater or equal'},{ oper:'bw', text:'begins with'},{ oper:'bn', text:'does not begin with'},{ oper:'in', text:'is in'},{ oper:'ni', text:'is not in'},{ oper:'ew', text:'ends with'},{ oper:'en', text:'does not end with'},{ oper:'cn', text:'contains'},{ oper:'nc', text:'does not contain'}]
        });
        $grid.jqGrid('navGrid','#pager',{edit: false, add: false, del: false, view: true});
        $grid.jqGrid('navButtonAdd', '#pager', {
            caption: "",
            buttonicon: "ui-icon-calculator",
            title: "Choose columns",
            onClickButton: function () {
                $(this).jqGrid('columnChooser', {
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                            saveColumnState.call(this, perm);
                        }
                    }
                });
            }
        });
        $grid.jqGrid('navButtonAdd', '#pager', {
            caption: "",
            buttonicon: "ui-icon-closethick",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                removeObjectFromLocalStorage(myColumnStateName($(this)));
                window.location.reload();
            }
        });
    }
};

var functionsMapping = {
    // here we define the implementations of the custom formatter which we use
    "textFormatter": function (cellValue, opts, rowObject) {
        return cellValue;
        //return "<div>" + cellValue + "</div>";
    },
    "imagePreview" : function (cellValue, opts, rowObjects) {
        return '<a href="'+cellValue+'" title="/images/html/?url='+encodeURIComponent(cellValue)+'" target="_new">'+cellValue+'</a>';
    }
};

//]]>

