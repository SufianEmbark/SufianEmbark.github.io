using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using System.IO;

public class MenuManager : MonoBehaviour
{
    public static MenuManager Instance;

    public string finalName;
    public string bestName;
    public int bestScore;

    private void Awake()
    {

        if (Instance != null)
        {
            Destroy(gameObject);
            return;
        }

        Instance = this;
        DontDestroyOnLoad(gameObject);
        LoadBestName();
        LoadBestScore();
    }

    [System.Serializable]
    public class SaveData
    {
        public string finalName;
        public string bestName;
        public int bestScore;

    }

    public void SaveName()
    {
        SaveData data = new SaveData();
        data.finalName = finalName;

        string json = JsonUtility.ToJson(data);

        File.WriteAllText(Application.persistentDataPath + "/savefile.json", json);
    }

    public void SaveBestName()
    {
        SaveData nameData = new SaveData();
        nameData.bestName = bestName;

        string nameJson = JsonUtility.ToJson(nameData);

        File.WriteAllText(Application.persistentDataPath + "/savefile.json", nameJson);
    }

    public void SaveBestScore()
    {
        SaveData scoreData = new SaveData();
        scoreData.bestScore = bestScore;

        string scoreJson = JsonUtility.ToJson(scoreData);

        File.WriteAllText(Application.persistentDataPath + "/savefile.json", scoreJson);
    }

    public void LoadBestName()
    {
        string path = Application.persistentDataPath + "/savefile.json";
        if (File.Exists(path))
        {
            Debug.Log("se ha encontrado la ruta del nombre");//kita
            string nameJson = File.ReadAllText(path);
            SaveData nameData = JsonUtility.FromJson<SaveData>(nameJson);
            Debug.Log("se ha encontrado el nombre " + nameData.bestName);//kita
            bestName = nameData.bestName;
        }
    }

    public void LoadBestScore()
    {
        string path = Application.persistentDataPath + "/savefile.json";
        if (File.Exists(path))
        {
            Debug.Log("se ha encontrado la ruta del numero");//kita
            string scoreJson = File.ReadAllText(path);
            SaveData scoreData = JsonUtility.FromJson<SaveData>(scoreJson);
            Debug.Log("se ha encontrado el numero" + scoreData.bestScore);//kita
            bestScore = scoreData.bestScore;
        }
    }

}