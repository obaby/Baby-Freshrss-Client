import json
import time
import html2text

import requests
from bs4 import BeautifulSoup
from pyfiglet import Figlet

FRESHRSS_HOST = 'http://freshrss.h4ck.org.cn' #不带最后的/
USERNAME = 'obaby'
PASSWD = '1234567890'
labels = ['集美们'] # 输出的订阅标签list
WRITE_TO_FILE_COUNT = 60
SUB_MAX_ITEMS_COUNT = 2
JSON_FILE_PATH = '/home/wwwroot/h4ck.org.cn/rss.json'

def print_hi(name):
    print('*' * 100)
    # f = Figlet(font='slant')
    f = Figlet()
    print(f.renderText('obaby@mars'))
    print('FreshRss Clien')
    print('Verson: 23.12.24')
    print('闺蜜圈：https://dayi.ma')
    print('Blog: http://oba.by')
    print('欢迎帮姐姐推广闺蜜圈啊')
    print('*' * 100)


def get_token():
    print('[*] Login to get token.')
    resp = requests.get(FRESHRSS_HOST+'/api/greader.php/accounts/ClientLogin?Email='+USERNAME+'&Passwd='+ PASSWD).text
    # print(resp)
    token = resp.split('Auth=')[1].replace('\r','').replace('\n','')
    print('[*] Token =',token)
    print('[*] ', '-'*100 )
    return token

def get_rss_items_list(token):
    heaers = {
        "Authorization":"GoogleLogin auth=" +token,
        "Accept-Encoding":"gzip, deflate",
        "User-Agent": "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; Tablet PC 2.0; wbx 1.0.0; wbxapp 1.0.0; Zoom 3.6.0)",
    }
    resp = requests.get(FRESHRSS_HOST+'/api/greader.php/reader/api/0/stream/contents/reading-list?n=99999999999&output=json', headers=heaers).text

    # print(resp)
    return resp

def get_rss_addr_list(token):
    heaers = {
        "Authorization": "GoogleLogin auth=" + token,
        "User-Agent": "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; Tablet PC 2.0; wbx 1.0.0; wbxapp 1.0.0; Zoom 3.6.0)",
    }
    resp = requests.get(
        FRESHRSS_HOST+'/api/greader.php/reader/api/0/subscription/list?output=json&n=10000',
        headers=heaers).text

    # print(resp)
    return resp

# Press the green button in the gutter to run the script.
if __name__ == '__main__':
    print_hi('obaby')

    token = get_token()
    # body=get_rss_items_list(token)

    addr_list = get_rss_addr_list(token)
    ajs = json.loads(addr_list)
    # print(ajs)

    subs = ajs['subscriptions']
    print('[*] All subscriptions=',len(subs) )
    selected_usb = {}
    for s in subs:
        cats = s['categories']
        for c in cats:
            if c['label'] in labels:
                # sd = {s['id']:{
                #     'title':s['title'],
                #     'iconUrl':s['iconUrl']
                # }}
                selected_usb[s['id']]={
                    'title':s['title'],
                    'iconUrl':s['iconUrl']
                }
    # print(selected_usb)
    print('[*] Selected label subscriptions=', len(selected_usb))
    body = get_rss_items_list(token)
    js = json.loads(body)
    print('[*] Total rss item count=',len(js['items']))

    sub_count_list = {}
    items = js['items']
    items = sorted(items, key = lambda item:item['published'], reverse=True)
    formated_item = []
    for i in items:
        string_time = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(i['published']))
        # content = html2text.html2text(i['summary']['content'])
        soup = BeautifulSoup(i['summary']['content'], 'html.parser')
        content = soup.get_text()
        content = content.replace('\r','').replace('\n','')[:100] + '……'
        stream_id = i['origin']['streamId']
        if not stream_id in selected_usb.keys():
            # print(stream_id)
            continue
        ni = {
            "site_name": i['origin']['title'],
            "title": i['title'],
            "link": i['alternate'][0]['href'],
            "time": string_time,
            "description": content,
            "icon": selected_usb[i['origin']['streamId']]['iconUrl'],
            "published":i['published']
        }
        # formated_item.append(ni)
        if stream_id in sub_count_list.keys():
            if sub_count_list[stream_id] >=SUB_MAX_ITEMS_COUNT:
                continue
            else:
                formated_item.append(ni)
                sub_count_list[stream_id] += 1
        else:
            formated_item.append(ni)
            sub_count_list[stream_id] =1


    # print(formated_item)
    # print(len(formated_item))
    print('[*] Selected labeled rss item count=',len(js['items']))
    print('[*] Write json to file......')
    for ff in formated_item:

        print(ff['time'], ff['title'])
    with open('JSON_FILE_PATH', 'w',encoding='utf8') as f:
        # 使用json.dump()函数将序列化后的JSON格式的数据写入到文件中
        json.dump(formated_item[:WRITE_TO_FILE_COUNT], f, indent=4,ensure_ascii=False)
        print('[*] Write json to file done')
    print('[*] Write to file items count=', WRITE_TO_FILE_COUNT)
    print('[*] Sub items max count=', SUB_MAX_ITEMS_COUNT)

    print('[*] All finished.')
    print('~' * 200)