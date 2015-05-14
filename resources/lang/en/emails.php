<?php
/**
 * subjects.php
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
return [
    'subject'                     => "Subject",
    'subjects'                    => "Subjects",
    'import_complete'             => "Project Data Import Complete",
    'import_complete_message'     => "If duplicates or rejects exist, you will find their information in attached csv files. Duplicates are subjects that already exist in the project. Rejected subjects mean an image url could not be found in the data.",
    'import_message'              => "The following data import has been completed. OCR processing may take a longer and you will receive an email when it is complete.",
    'thank_you'                   => "Thank you",
    'signature'                   => "The Biospex Team",
    'welcome'                     => "Welcome",
    'expedition_complete'         => "Biospex process completed: :expedition",
    'expedition_complete_message' => "The expedition \":expedition\" has been processed successfully. If a download file was created during this process, you may access the link on the Expedition view page. If there were errors, an attachment will be included in this email.",
    'missing_images_subject'      => "Missing image information",
    'missing_images'              => "The below images were unable to be found. Missing image ids were missing the url column in the csv file. Missing image urls were images we were unable to retrieve or convert.",
    'missing_img_ids'             => "Missing Image Ids:",
    'missing_img_urls'            => "Missing Image Urls",
    'group_invite_subject'        => "Bisopex Group Invite",
    'group_invite_message'        => "You have been invited to join the Biospex group :group. Please click the link below to register using this email address.",
    'ocr_complete'                => "OCR Process Complete",
    'contact'                     => "Contact Form",
    'contact_subject'             => "Biospex Contact Form",
    'contact_first'               => "First Name",
    'contact_last'                => "Last Name",
    'contact_email'               => "Email",
    'contact_message'             => "Message",

    'error'                       => "Biospex error report.",
    'error_import'                => "An error has occurred during data import.",
    'error_import_process'        => "Unable to process subject import.<br />Id: :id<br />Message: :message<br />Trace: :trace",
    'error_message'               => "Error Message:",
    'error_process'               => "Expedition id :id could not be found during processing.",
    'error_create_dir'            => "Unable to create directory: :directory",
    'error_write_dir'             => "Unable to make directory writable: :directory",
    'error_save_file'             => "Unable to save file: :directory",
    'error_workflow_manager'      => "An error occurred while processing :class using workflow id :id. Message - :error",
    'error_core_type'             => "Error querying core type from meta file.",
    'error_core_file_missing'     => "Could not determine core file from meta file.",
    'error_csv_row_count'         => "Header column count does not match row count. :headers headers / :rows rows",
    'error_csv_build_header'      => "Undefined index for :key => :qualified when building header for csv import.",
    'error_csv_core_delimiter'    => "CSV core delimiter is empty.",
    'error_csv_ext_delimiter'     => "CSV extension delimiter is empty.",
    'error_load_xml'              => "Unable to load dom document for meta id :id",
    'error_build_image_dir'       => "No images were retrieved during build for Expedition Id :id.",

    'error_ocr_curl'              => "Ocr Queue error sending file for record :id. Message: :message",
    'error_ocr_request'           => "Ocr failed when requesting file for record :id.",
    'error_ocr_header'            => "Ocr header response reported error while processing file.",
    'error_ocr_queue'             => "Process error - :id, :message, :url.",
    'error_ocr_stuck_queue'       => "The queue job with id of :id has :tries tries and may need some oversight.",
    'failed_job_subject'          => "Failed Job Report.",
    'failed_job_message'          => "Job queue :id has failed. ",
];