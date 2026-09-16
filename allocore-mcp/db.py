import pymysql
import json
from typing import Any, Dict, List, Optional
from config import DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

def get_connection():
    return pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USERNAME,
        password=DB_PASSWORD,
        database=DB_DATABASE,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True
    )

def query(sql: str, params: Optional[tuple] = None) -> List[Dict[str, Any]]:
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(sql, params or ())
            return cursor.fetchall()
    finally:
        conn.close()

def query_one(sql: str, params: Optional[tuple] = None) -> Optional[Dict[str, Any]]:
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(sql, params or ())
            return cursor.fetchone()
    finally:
        conn.close()

def execute(sql: str, params: Optional[tuple] = None) -> int:
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            affected = cursor.execute(sql, params or ())
            return affected
    finally:
        conn.close()

def execute_last_id(sql: str, params: Optional[tuple] = None) -> int:
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(sql, params or ())
            return cursor.lastrowid
    finally:
        conn.close()
