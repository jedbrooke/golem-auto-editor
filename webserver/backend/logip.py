import sys
import sqlite3
import json
import os
DB_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)),"iplog.db")

"""
SCHEMA:
TABLE ip_logs (ip string, requests int)
TABLE indv_logs (id string, requests int)
"""

if __name__ == '__main__':
    ip = sys.argv[1]
    user_agent = sys.argv[2]
    indv_id = ip + user_agent
    try:
        with sqlite3.connect(DB_PATH) as con:
            cur = con.cursor()
            
            # update ip log
            cur.execute("SELECT * FROM ip_logs WHERE ip=:addr",{"addr": ip})
            row = cur.fetchone()
            if row:
                cur.execute("UPDATE ip_logs SET requests=:r WHERE ip=:addr",{"r": row[1] + 1,"addr": ip})
            else:
                cur.execute("INSERT INTO ip_logs values (?,?)",(ip,1))
            con.commit()
            cur.execute("SELECT * FROM ip_logs WHERE ip=:addr",{"addr": ip})
            ip_requests = cur.fetchone()[1]
            
            # update indv logs
            cur.execute("SELECT * FROM indv_logs WHERE id=:indv_id",{"indv_id":indv_id})
            row = cur.fetchone()
            if row:
                cur.execute("UPDATE indv_logs SET requests=:r WHERE id=:indv_id",{"r": row[1] + 1, "indv_id": indv_id})
            else:
                cur.execute("INSERT INTO indv_logs values (?,?)",(indv_id,1))
            con.commit()
            cur.execute("SELECT * FROM indv_logs WHERE id=:indv_id",{"indv_id":indv_id})

            id_requests = cur.fetchone()[1]
            
            print(json.dumps({"ip": ip_requests, "indv": id_requests}))
    except Exception as e:
        print(e)
        