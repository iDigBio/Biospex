/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$.jgrid = $.jgrid || {};
$.jgrid.no_legacy_api = true;
$.jgrid.useJSON = true;
$.jgrid.defaults.responsive = true;
$.jgrid.cellattr = $.jgrid.cellattr || {};

$(function () {
    if ($('#jqGridTable').length > 0) {

        "use strict";
        let dataUrl = Laravel.dataUrl;
        let model = JSON.parse(Laravel.model), cm = model.colModel, cn = model.colNames;

        let $grid = $("#jqGridTable");

        mapFormatter(cm);

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
            columnChooser = function () {
                $grid.jqGrid('columnChooser', {
                    classname: "columnChooser",
                    modal: true,
                    width: 600,
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                        }
                    }
                });
                $('.ui-multiselect ul.selected').height('500px');
                $('.ui-multiselect ul.available').height('500px');
            },
            eraseSettings = function () {
                removeObjectFromLocalStorage(myColumnStateName);
                window.location.reload();
            },
            firstLoad = true;

        myColumnsState = restoreColumnState(cm);
        isColState = typeof (myColumnsState) !== 'undefined' && myColumnsState !== null;

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
            url: dataUrl,
            mtype: "GET",
            datatype: "json",
            colNames: cn,
            colModel: cm,
            cmTemplate: {autoResizable: true, editable: true},
            guiStyle: "bootstrap4",
            iconSet: "fontAwesome",
            rowNum: 25,
            page: isColState ? myColumnsState.page : 1,
            search: isColState ? myColumnsState.search : false,
            postData: isColState ? {filters: myColumnsState.filters} : {},
            sortname: isColState ? myColumnsState.sortname : '_id',
            sortorder: isColState ? myColumnsState.sortorder : 'desc',
            autoResizing: {compact: true},
            autoWidthColumns: true,
            autowidth: true,
            rowList: [25, 50, 100, 500, 1000],
            viewrecords: true,
            autoencode: true,
            sortable: true,
            toppager: true,
            pager: true,
            searching: {searchOnEnter: true, searchOperators: true},
            loadComplete: function () {
                if (firstLoad) {
                    firstLoad = false;
                    if (isColState) {
                        $(this).jqGrid("remapColumns", myColumnsState.permutation, true);
                    }
                }
                saveColumnState.call($(this), this.p.remapColumns);
            }
        })
            .jqGrid("navGrid", {add: false, edit: false, del: false, search: true}, {}, {}, {}, {
                afterShowSearch: function ($form) {
                    $form.closest(".ui-jqdialog").position({
                        of: window, // or any other element
                        my: "center center",
                        at: "center center"
                    });
                },
                width: 700,
                multipleSearch: true,
                recreateFilter: true
            })
            .jqGrid("navButtonAdd", {
                caption: '',
                buttonicon: "fas fa-columns",
                title: "Choose columns",
                onClickButton: columnChooser
            })
            .jqGrid("navButtonAdd", {
                caption: '',
                buttonicon: "fas fa-eraser",
                title: "Clear saved grid's settings",
                onClickButton: eraseSettings
            })
            .jqGrid("filterToolbar")
            .jqGrid("gridResize");

    }
});


/**
 * Map formatter
 * @param column
 */
function mapFormatter(column) {
    let functionsMapping = {
        "link": function (cellValue, opts, rowObjects) {
            return '<a href="/record/' + cellValue + '" target="_new">' + cellValue + '</a>';
        }
    };

    for (let i = 0; i < column.length; i++) {
        let col = column[i];
        if (col.hasOwnProperty("formatter") &&
            functionsMapping.hasOwnProperty(col.formatter)) {
            col.formatter = functionsMapping[col.formatter];
        }
    }
}
