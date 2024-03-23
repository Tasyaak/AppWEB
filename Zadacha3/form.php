<form action="" method="post" class="form">
  <div>
        <div class="head">
          <h2><b>Форма обратной связи</b></h2>
        </div>

        <div>
          <label> <input name="fio" class="input" placeholder="ФИО" /> </label>
        </div>

        <div>
          <label> <input type="tel" name="phone" class="input" list="tel-list" placeholder="Номер телефона" /> </label>
        </div>

        <div>
          <label> <input name="email" type="email" class="input" placeholder="Введите почту" /> </label>
        </div>

        <div>
          <label>
            <input name="date" class="input" type="date" />
          </label>
        </div>

        <div>
          <div>Пол</div>
          <div class="mb-1">
            <label> <input class="ml-2" type="radio" checked="checked" name="radio-group-1" value="Значение1" /> М </label>
            <label> <input class="ml-4" type="radio" name="radio-group-1" value="Значение2" /> Ж </label>
          </div>
        </div>

        <div>
          <label class="input">
            <div>Любимый язык программирования</div>
            <select class="my-2" name="languagel" multiple="multiple">
              <option>Pascal</option>
              <option>C</option>
              <option>C++</option>
              <option>JavaScript</option>
              <option>PHP</option>
              <option>Python</option>
              <option>Java</option>
              <option>Haskel</option>
              <option>Clojure</option>
              <option>Scala</option>
            </select>
          </label>
        </div>

        <div class="my-2">
          <div>Биография</div>
          <label>
            <textarea class="input" name="biography" placeholder="Биография"> </textarea>
          </label>
        </div>

        <div>
          <label> <input type="checkbox" name="check_mark" /> с контрактом ознакомлен(а) </label>
        </div>

        <button type="submit" class="button my-3">Отправить</button>
  </div>
</form>

</body>
</html>