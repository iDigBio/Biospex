/**
 * Created by Robert on 11/19/2014.
 */
$.jgrid.formatter.integer.thousandsSeparator = ',';
$.jgrid.formatter.number.thousandsSeparator = ',';
$.jgrid.formatter.currency.thousandsSeparator = ',';
$(document).ready(function () {
    'use strict';
    var projectId = $("#projectId").val();
    var expeditionId = $("#expeditionId").val();
    var $grid = $("#jqgrid"),
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
        numberTemplate = {formatter: 'number', align: 'right', sorttype: 'number',
            searchoptions: { sopt: numberSearchOptions }},
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
        cm = [
            {name: '_id',index: '_id', hidden: true, editable: true, editrules: {edithidden: true}},
            {name: 'id',index: 'id', width:150, sortable:false},
            {name: 'accessURI', index: 'accessURI', width: 150, editable: false, sortable: false, formatter: 'showlink',
                formatoptions: { baseLinkUrl: 'javascript:', showAction: "link('", addParam: "');"}
            },
            {name: 'ocr',index: 'ocr', width:150, sortable:false},

        ],
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
        myColumnStateName = 'ColumnChooserAndLocalStorage1.colState',
        saveColumnState = function () {
            var colModel = this.jqGrid('getGridParam', 'colModel'), i, l = colModel.length, colItem, cmName,
                postData = this.jqGrid('getGridParam', 'postData'),
                columnsState = {
                    search: this.jqGrid('getGridParam', 'search'),
                    page: this.jqGrid('getGridParam', 'page'),
                    rowNum: this.jqGrid('getGridParam', 'rowNum'),
                    sortname: this.jqGrid('getGridParam', 'sortname'),
                    sortorder: this.jqGrid('getGridParam', 'sortorder'),
                    permutation: this.jqGrid('getGridParam', 'remapColumns'),
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
            saveObjectInLocalStorage(myColumnStateName, columnsState);
        },
        myColumnsState,
        isColState,
        restoreColumnState = function (colModel) {
            var colItem, i, l = colModel.length, colStates, cmName,
                columnsState = getObjectFromLocalStorage(myColumnStateName);

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
        firstLoad = true;

    myColumnsState = restoreColumnState(cm);
    isColState = typeof (myColumnsState) !== 'undefined' && myColumnsState !== null;

    $grid.jqGrid({
        mtype: "GET",
        url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids",
        datatype: "json",
        colNames: ['_id','ID', 'AccessUri', 'OCR'],
        colModel: cm,
        rowNum: isColState ? myColumnsState.rowNum : 10,
        rowList: [5, 10, 20],
        pager: '#jqpager',
        gridview: true,
        search: isColState ? myColumnsState.search : false,
        postData: isColState ? { filters: myColumnsState.filters } : {},
        sortname: isColState ? myColumnsState.sortname : 'invdate',
        sortorder: isColState ? myColumnsState.sortorder : 'desc',
        rownumbers: true,
        ignoreCase: true,
        shrinkToFit: false,
        viewrecords: true,
        loadonce: false,
        height: 'auto',
        caption: "Subjects",
        jsonReader: {
            root: "rows",
            page: "page",
            total: "total",
            records: "records"
        },
        loadBeforeSend: function(jqXHR) {
            jqXHR.setRequestHeader("X-CSRF-Token", $('meta[name="_token"]').attr('content'));
        },
        loadComplete: function () {
            var $this = $(this);
            if (firstLoad) {
                firstLoad = false;
                if (isColState && myColumnsState.permutation.length > 0) {
                    $this.jqGrid("remapColumns", myColumnsState.permutation, true);
                }
                if (typeof (this.ftoolbar) !== "boolean" || !this.ftoolbar) {
                    // create toolbar if needed
                    $this.jqGrid('filterToolbar',
                        {stringResult: true, searchOnEnter: true, defaultSearch: myDefaultSearch});
                }
            }
            refreshSerchingToolbar($this, myDefaultSearch);
            saveColumnState.call($this);
        },
        resizeStop: function () {
            saveColumnState.call($grid);
        }
    });
    $.extend($.jgrid.search, {
        multipleSearch: true,
        multipleGroup: true,
        recreateFilter: true,
        closeOnEscape: true,
        closeAfterSearch: true,
        overlay: 0
    });
    $grid.jqGrid('navGrid', '#jqpager', {edit: false, add: false, del: false});
    $grid.jqGrid('navButtonAdd', '#jqpager', {
        caption: "",
        buttonicon: "ui-icon-calculator",
        title: "choose columns",
        onClickButton: function () {
            $(this).jqGrid('columnChooser', {
                done: function (perm) {
                    if (perm) {
                        this.jqGrid("remapColumns", perm, true);
                        saveColumnState.call(this);
                    }
                }
            });
        }
    });
    $grid.jqGrid('navButtonAdd', '#jqpager', {
        caption: "",
        buttonicon: "ui-icon-closethick",
        title: "clear saved grid's settings",
        onClickButton: function () {
            removeObjectFromLocalStorage(myColumnStateName);
            window.location.reload();
        }
    });
});


function link(id) {
    var row = id.split("=");
    var row_ID = row[1];
    var uri= $("#jqgrid").getCell(row_ID, 'accessURI');
    window.open(uri);
}