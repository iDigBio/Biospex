/**
 * Created by Robert on 11/20/2014.
 */
$(function() {
    if ($("#jqgrid").length) {
        var projectId = $("#projectId").val();
        var expeditionId = $("#expeditionId").val();
        $.ajax({
            type: "GET",
            url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids/load",
            dataType: "json",
            success: function(result)
            {
                var colN = result.colNames;
                var colM = result.colModel;

                var $grid = $("#jqgrid"),
                    myDefaultSearch = 'cn',
                    getColumnIndex = function (grid, columnIndex) {
                        var cm = grid.jqGrid('getGridParam', 'colModel'), i, l = colModel.length;
                        for (i = 0; i < l; i++) {
                            if ((cm[i].index || cm[i].name) === columnIndex) {
                                return i; // return the colModel index
                            }
                        }
                        return -1;
                    },
                    refreshSearchingToolbar = function ($grid, myDefaultSearch) {
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
                    jsonReader : {
                        repeatitems: false,
                        root:"rows",
                        page: "page",
                        total: "total",
                        records: "records",
                        id: "_id"
                    },
                    url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids",
                    datatype: 'json',
                    mtype: 'GET',
                    colNames:colN,
                    colModel :colM,
                    pager: jQuery('#jqpager'),
                    rowNum: isColState ? myColumnsState.rowNum : 10,
                    rowList: [10, 20, 50],
                    search: isColState ? myColumnsState.search : false,
                    postData: isColState ? { filters: myColumnsState.filters } : {},
                    sortname: isColState ? myColumnsState.sortname : 'id',
                    sortorder: isColState ? myColumnsState.sortorder : 'desc',
                    viewrecords: true,
                    gridview: true,
                    shrinkToFit: false,
                    width: null,
                    emptyrecords: "No records to view",
                    rownumbers: true,
                    viewrecords: true,
                    autoencode: true,
                    loadonce: false,
                    height: "auto",
                    caption: "Subjects",
                    gridComplete: function () {
                        $(this).parent().append('<span id="widthTest" />');
                        gridName = this.id;
                        $('#gbox_' + gridName + ' .ui-jqgrid-htable,#' + gridName).css('width', 'inherit');
                        $('#' + gridName).parent().css('width', 'inherit');
                        var columnNames = $("#" + gridName).jqGrid('getGridParam', 'colModel');
                        var thisWidth;
                        // Loop through Cols
                        for (var itm = 0, itmCount = columnNames.length; itm < itmCount; itm++) {
                            var curObj = $('[aria-describedby=' + gridName + '_' + columnNames[itm].name + ']');
                            var thisCell = $('#' + gridName + '_' + columnNames[itm].name + ' div');
                            $('#widthTest').html(thisCell.text()).css({
                                'font-family': thisCell.css('font-family'),
                                'font-size': thisCell.css('font-size'),
                                'font-weight': thisCell.css('font-weight')
                            });
                            var maxWidth = Width = $('#widthTest').width() + 24;
                            //var maxWidth = 0;
                            // Loop through Rows
                            for (var itm2 = 0, itm2Count = curObj.length; itm2 < itm2Count; itm2++) {
                                var thisCell = $(curObj[itm2]);
                                $('#widthTest').html(thisCell.html()).css({
                                    'font-family': thisCell.css('font-family'),
                                    'font-size': thisCell.css('font-size'),
                                    'font-weight': thisCell.css('font-weight')
                                });
                                thisWidth = $('#widthTest').width();
                                if (thisWidth > maxWidth) maxWidth = thisWidth;
                            }
                            $('#' + gridName + ' .jqgfirstrow td:eq(' + itm + '), #' + gridName + '_' + columnNames[itm].name).width(maxWidth).css('min-width', maxWidth);
                        }
                        $('#widthTest').remove();
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
                        refreshSearchingToolbar($this, myDefaultSearch);
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
            }
        });
    }
});

