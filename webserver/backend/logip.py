import sys
import sqlite3
import datetime
import json
import os
DB_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)),"iplog.db")

"""
SCHEMA:
TABLE ip_logs (ip string, requests int, last_request text)
"""

if __name__ == '__main__':
    print("db path:",DB_PATH,end="<br>")
    ip = sys.argv[1]
    try:
        with sqlite3.connect(DB_PATH) as con:
            cur = con.cursor()
            cur.execute("SELECT * FROM ip_logs WHERE ip=:addr",{"addr": ip})
            row = cur.fetchone()
            if row:
                cur.execute("UPDATE ip_logs SET requests=:r WHERE ip=:addr",{"r": row[1] + 1,"addr":ip})
                cur.execute("UPDATE ip_logs SET last_request=:date WHERE ip=:addr",{"date": datetime.datetime.now(),"addr":ip})
            else:
                cur.execute("INSERT INTO ip_logs values (?,?,?)",(ip,1,datetime.datetime.now()))
            
            con.commit()
            
            cur.execute("SELECT * FROM ip_logs WHERE ip=:addr",{"addr": ip})
            print(json.dumps(cur.fetchone()))
    except Exception as e:
        print(e)
        