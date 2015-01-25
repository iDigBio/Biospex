<?php
/**
 * errors.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
return array(
    'error'                     => "Biospex error report.",
    'error_import'              => "An error has occurred during data import.",
    'error_message'             => "Error Message:",
    'error_process'             => "Expedition id :id could not be found during processing.",
    'error_create_dir'          => "Unable to create directory: :directory",
    'error_write_dir'           => "Unable to make directory writable: :directory",
    'error_save_file'           => "Unable to save file: :directory",
    'error_workflow_manager'    => "An error occurred while processing :class using workflow id :id. Message - :error",
    'error_xml_meta'            => "Unable to retrieve metadata for meta id :id.",
    'error_load_dom'            => "Unable to load dom document for meta id :id",
    'error_xpath'               => "Unable to perform xpath query using meta id :id.",
    'error_process_file_path'   => "Error: path for file :file does not exist.",
    'error_build_image_dir'     => "No images were retrieved during build for Expedition Id :id.",
    'error_delete_user'         => "Unable to delete user.",
    'error_ocr_queue'           => "Process error: :id, :message, :url.",
    'error_ocr_curl'            => "Ocr Queue error sending file.",
    'error_ocr_request'         => "Ocr failed on request file."
);