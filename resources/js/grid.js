$.jgrid.defaults.width = 780;
$.jgrid.defaults.responsive = true;
$.jgrid.cellattr = $.jgrid.cellattr || {};

let Grid = {};

$(function () {
    if ($("#jqGrid").length > 0) {
        'use strict';
        Grid.id = $(".jgrid").prop('id');
        Grid.obj = $("#" + Grid.id);
        Grid.loadUrl = Laravel.loadUrl;
        Grid.gridUrl = Laravel.gridUrl;

        $.ajax({
            type: "GET",
            url: Grid.loadUrl,
            dataType: "json",
            success: jqBuildGrid()
        });
    }
});

function jqBuildGrid() {

    return function (result) {
        let cm = result.colModel;
        let cn = result.colNames;

        let saveObjectInLocalStorage = function (storageItemName, object) {
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
        myColumnStateName = 'ColumnChooserAndLocalStorage.colState',
        saveColumnState = function (perm) {
            let colModel = this.jqGrid('getGridParam', 'colModel'), i, l = colModel.length, colItem, cmName,
                postData = this.jqGrid('getGridParam', 'postData'),
                columnsState = {
                    search: this.jqGrid('getGridParam', 'search'),
                    page: this.jqGrid('getGridParam', 'page'),
                    sortname: this.jqGrid('getGridParam', 'sortname'),
                    sortorder: this.jqGrid('getGridParam', 'sortorder'),
                    permutation: perm,
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
            let colItem, i, l = colModel.length, colStates, cmName,
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

        Grid.obj.jqGrid({
            jsonReader: {
                repeatitems: false,
                root: "rows",
                page: "page",
                total: "total",
                records: "records",
                cell: "",
                id: "_id"
            },
            url: Grid.gridUrl,
            mtype: "GET",
            datatype: "json",
            page: isColState ? myColumnsState.page : 1,
            search: isColState ? myColumnsState.search : false,
            postData: isColState ? { filters: myColumnsState.filters } : {},
            sortname: isColState ? myColumnsState.sortname : '_id',
            sortorder: isColState ? myColumnsState.sortorder : 'desc',

            //page: 1,
            colNames: cn,
            colModel: cm,
            rowNum: 20,
            gridview: true,
            rowList: [20, 50, 100],
            multiSort: true,
            sortable: true,
            //sortorder: 'asc',
            mulitpleSearch: true,
            //multiselect: false,
            //multiboxonly: true,
            viewrecords: true,
            shrinkToFit: true,
            autowidth: true,
            storeNavOptions: true,
            height: '100%',
            pager: "#pager",
            toppager: true,
            guiStyle: "bootstrap4",
            iconSet: "fontAwesome",
            //navGrid(element, {parameters}, prmEdit, prmAdd, prmDel, prmSearch, prmView);
            loadComplete: function () {
                if (firstLoad) {
                    firstLoad = false;
                    if (isColState) {
                        $(this).jqGrid("remapColumns", myColumnsState.permutation, true);
                    }
                }
                saveColumnState.call($(this), this.p.remapColumns);
            },
            resizeStop: function () {
                saveColumnState.call(Grid.obj, Grid.obj[0].p.remapColumns);
            }
        }).navGrid("#pager", {
                search: true,
                add: false,
                edit: false,
                del: false,
                refresh: true,
                closeOnEscape: true,
                closeAfterSearch: true,
                overlay: true,
                cloneToTop: true
            }, // {parameters}
            {}, // prmEdit
            {}, // prmAdd
            {}, // prmDel
            {
                top: 'auto',
                width: 700,
                multipleSearch: true,
                recreateFilter: true,
            }, // prmSearch
            {} // prmView
        ).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "fas fa-columns",
            title: "Choose columns",
            onClickButton: function () {
                Grid.obj.jqGrid('columnChooser', {
                    classname: "columnChooser",
                    modal: true,
                    width: 600,
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                            saveColumnState.call(this, perm);
                        }
                    }
                });
                $('.ui-multiselect ul.selected').height('500px');
                $('.ui-multiselect ul.available').height('500px');
            }
        }).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "fas fa-eraser",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                localStorage.clear();
                window.location.reload();
            }
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "fas fa-columns",
            title: "Choose columns",
            onClickButton: function () {
                Grid.obj.jqGrid('columnChooser', {
                    classname: "columnChooser",
                    modal: true,
                    width: 600,
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                            saveColumnState.call(this, perm);
                        }
                    }
                });
                $('.ui-multiselect ul.selected').height('500px');
                $('.ui-multiselect ul.available').height('500px');
            }
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "fas fa-eraser",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                removeObjectFromLocalStorage(myColumnStateName);
                window.location.reload();
            }
        });
    };
}

