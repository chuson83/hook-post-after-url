# Hook Post After Url

## 概要
ワードプレスプラグインです。  
インストール後有効化し、設定からURLを登録すると、記事を公開するタイミングでそのURLにアクセスします。  
API連携、更新通知などの利用を想定してます。

## 環境
環境：WordPress 4.8.1にて動作確認済み  
php: 5.6系

## セットアップ
cloneしたフォルダ（hook-post-after-url）を/wp-content/plugins/下に設置します。  
その後WordPress管理画面で有効化し、設定からURLを入力します。  
記事公開時にそのURLにアクセスします。