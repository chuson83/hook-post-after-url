<?php
/*
Plugin Name: Hook Post After Url
Plugin URI: 
Description: 記事公開後に指定のURLにアクセスするプラグインです。API連携や更新通知用に利用できます。有効にするとプラグインメニューに設定が追加されるので、指定のURLを設定してください。
Version: 1.0.0
Author:nakamura.hiroshi
Author URI: 
License: GPL2
*/

/*  Copyright 2017 nakamura.hiroshi (email : dev.sanzo83@gmail.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function hook_post_after_url($post_id){
    
    // リビジョン保存時はスルー
    if ( wp_is_post_revision( $post_id ) ){
        return;
    }

    // DBの設定値を取得します。
    $options = get_option( 'hook_post_after_url' );

    // httpアクセス
    curl_url($options['url_str1']);
    curl_url($options['url_str2']);
    curl_url($options['url_str3']);
}

function curl_url($url){
    if (empty($url)) {
        return false;
    }
    $post_title = get_the_title( $post_id );
    $post_url = get_permalink( $post_id );
    $content = get_post_field( 'post_content', $post_id );

    //cURLセッションを初期化する
    $ch = curl_init();

    //URLとオプションを指定する
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //URLの情報を取得する
    $res =  curl_exec($ch);

    return $res;
}

add_action('publish_post', 'hook_post_after_url');
add_action('deleted_post', 'hook_post_after_url');

/**
 * 設定用のクラスです。
 * ※ http://codex.wordpress.org/Creating_Options_Pages を再構成しています。
 */
 class SettingsPage
 {
     /** 設定値 */
     private $options;
  
     /**
      * 初期化処理です。
      */
     public function __construct()
     {
         // メニューを追加します。
         add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
         // ページの初期化を行います。
         add_action( 'admin_init', array( $this, 'page_init' ) );
     }
  
     /**
      * メニューを追加します。
      */
     public function add_plugin_page()
     {
         // add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
         //   $page_title: 設定ページの<title>部分
         //   $menu_title: メニュー名
         //   $capability: 権限 ( 'manage_options' や 'administrator' など)
         //   $menu_slug : メニューのslug
         //   $function  : 設定ページの出力を行う関数
         //   $icon_url  : メニューに表示するアイコン
         //   $position  : メニューの位置 ( 1 や 99 など )
         add_submenu_page( 'plugins.php','Hook Post After Url設定', 'Hook Post After Url設定', 'manage_options', 'hook-post-after-url_setting', array( $this, 'create_admin_page' ) );
  
         // 設定のサブメニューとしてメニューを追加する場合は下記のような形にします。
         // add_options_page( 'テスト設定', 'テスト設定', 'administrator', 'test_setting', array( $this, 'create_admin_page' ) );
     }
  
     /**
      * 設定ページの初期化を行います。
      */
     public function page_init()
     {
         // 設定を登録します(入力値チェック用)。
         // register_setting( $option_group, $option_name, $sanitize_callback )
         //   $option_group      : 設定のグループ名
         //   $option_name       : 設定項目名(DBに保存する名前)
         //   $sanitize_callback : 入力値調整をする際に呼ばれる関数
         register_setting( 'hook_post_after_url', 'hook_post_after_url', array( $this, 'sanitize' ) );
  
         // 入力項目のセクションを追加します。
         // add_settings_section( $id, $title, $callback, $page )
         //   $id       : セクションのID
         //   $title    : セクション名
         //   $callback : セクションの説明などを出力するための関数
         //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
         add_settings_section( 'hook_post_after_url_section_id', '', '', 'hook_post_after_url' );
  
         // 入力項目のセクションに項目を1つ追加します
         // add_settings_field( $id, $title, $callback, $page, $section, $args )
         //   $id       : 入力項目のID
         //   $title    : 入力項目名
         //   $callback : 入力項目のHTMLを出力する関数
         //   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
         //   $section  : セクションのID (add_settings_section()の$idと同じものにする)
         //   $args     : $callbackの追加引数 (必要な場合のみ指定)
         add_settings_field( 'url_str1', 'URL1', array( $this, 'url_callback' ), 'hook_post_after_url', 'hook_post_after_url_section_id',array('id' => 'url_str1', 'name'=> 'hook_post_after_url[url_str1]'));
         
         add_settings_field( 'url_str2', 'URL2', array( $this, 'url_callback' ), 'hook_post_after_url', 'hook_post_after_url_section_id' ,array('id' => 'url_str2', 'name'=> 'hook_post_after_url[url_str2]'));
         
         add_settings_field( 'url_str3', 'URL3', array( $this, 'url_callback' ), 'hook_post_after_url', 'hook_post_after_url_section_id' ,array('id' => 'url_str3', 'name'=> 'hook_post_after_url[url_str3]'));
         
     }
  
     /**
      * 設定ページのHTMLを出力します。
      */
     public function create_admin_page()
     {
         // 設定値を取得します。
         $this->options = get_option( 'hook_post_after_url' );
         ?>
         <div class="wrap">
             <h2>Hook Post After Url設定</h2>
             <?php
             // add_options_page()で設定のサブメニューとして追加している場合は
             // 問題ありませんが、add_menu_page()で追加している場合
             // options-head.phpが読み込まれずメッセージが出ない(※)ため
             // メッセージが出るようにします。
             // ※ add_menu_page()の場合親ファイルがoptions-general.phpではない
             global $parent_file;
             if ( $parent_file != 'options-general.php' ) {
                 require(ABSPATH . 'wp-admin/options-head.php');
             }
             ?>
             <form method="post" action="options.php">
             <?php
                 // 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
                 settings_fields( 'hook_post_after_url' );
                 // 入力項目を出力します(設定ページのslugを指定)。
                 do_settings_sections( 'hook_post_after_url' );
                 // 送信ボタンを出力します。
                 submit_button();
             ?>
             </form>
         </div>
         <?php
     }
  
     /**
      * 入力項目(「URL」)のHTMLを出力します。
      */
     public function url_callback($args)
     {
         // 値を取得
         $urlStr = isset( $this->options[$args['id']] ) ? $this->options[$args['id']] : '';
         // nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
         ?>
         <input type="text" size=100 id="<?php esc_attr_e( $args['id'] ) ?>" name="<?php esc_attr_e( $args['name'] ) ?>" value="<?php esc_attr_e( $urlStr ) ?>" />
         <?php
     }
  
     /**
      * 送信された入力値の調整を行います。
      *
      * @param array $input 設定値
      */
     public function sanitize( $input )
     {

        // DBの設定値を取得します。
        $this->options = get_option( 'hook_post_after_url' );
  
        $new_input = array();
  
        // メッセージがある場合値を調整
        if( isset( $input['url_str1'] ) && trim( $input['url_str1'] ) !== '' ) {
            if(preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $input['url_str1'])){
                $new_input['url_str1'] = sanitize_text_field( $input['url_str1'] );
            } else {
                add_settings_error( 'hook_post_after_url', 'url_str1', '【URL1】urlの形式ではありません' );
            }
        } else {
            // メッセージがない場合エラーを出力
            // add_settings_error( $setting, $code, $message, $type )
            //   $setting : 設定のslug
            //   $code    : エラーコードのslug (HTMLで'setting-error-{$code}'のような形でidが設定されます)
            //   $message : エラーメッセージの内容
            //   $type    : メッセージのタイプ。'updated' (成功) か 'error' (エラー) のどちらか
            add_settings_error( 'hook_post_after_url', 'url_str1', 'url1を入力してください' );
  
            // 値をDBの設定値に戻します。
            $new_input['url_str1'] = isset( $this->options['url_str1'] ) ? $this->options['url_str1'] : '';
        }
        if( isset( $input['url_str2'] ) && trim( $input['url_str2'] ) !== '' ) {
            if(preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $input['url_str2'])){
                $new_input['url_str2'] = sanitize_text_field( $input['url_str2'] );
            } else {
                add_settings_error( 'hook_post_after_url', 'url_str2', '【URL2】urlの形式ではありません' );
            }
        }
        if( isset( $input['url_str3'] ) && trim( $input['url_str3'] ) !== '' ) {
            if(preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $input['url_str3'])){
                $new_input['url_str3'] = sanitize_text_field( $input['url_str3'] );
            } else {
                add_settings_error( 'hook_post_after_url', 'url_str3', '【URL3】urlの形式ではありません' );
            }
        }
  
         return $new_input;
     }
  
 }
  
 // 管理画面を表示している場合のみ実行します。
 if( is_admin() ) {
     $test_settings_page = new SettingsPage();
 }